<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import { Icon, Popover, ToggleGroup, ToggleItem, Widget } from "@statamic/cms/ui";
import BarList from "./BarList.vue";
import EventsPanel from "./EventsPanel.vue";
import SegmentBar from "./SegmentBar.vue";
import TrendChart from "./TrendChart.vue";
import { countryFlag, countryName, delta, formatCount, formatDuration, formatFull, METRICS, timeAgo } from "./format";

/**
 * The site's Fathom analytics on the dashboard (Fleet Agent's
 * fathom_analytics widget). Figures come from Fleet through this site's own
 * CP routes; Fleet caches them, so switching ranges back and forth is cheap.
 */
const props = defineProps({
    title: { type: String, default: "Analytics" },
    defaultRange: { type: String, default: "30d" },
    urls: { type: Object, required: true }, // { report, events, event, live }
    siteUrl: { type: String, required: true },
    settingsUrl: { type: String, required: true },
    eventsOn: { type: Boolean, default: true },
});

const RANGES = [
    { value: "today", label: "Today", long: "today so far" },
    { value: "7d", label: "7D", long: "the last 7 days" },
    { value: "30d", label: "30D", long: "the last 30 days" },
    { value: "90d", label: "90D", long: "the last 90 days" },
    { value: "12m", label: "12M", long: "the last 12 months" },
];

const DEVICE_COLORS = { Desktop: "var(--fa-cat-1)", Phone: "var(--fa-cat-2)", Tablet: "var(--fa-cat-3)" };

const remembered = (key, fallback, allowed) => {
    try {
        const value = window.localStorage.getItem(`fp-analytics:${key}`);
        return allowed.includes(value) ? value : fallback;
    } catch {
        return fallback;
    }
};

const remember = (key, value) => {
    try {
        window.localStorage.setItem(`fp-analytics:${key}`, value);
    } catch {
        // Private windows and blocked storage: nothing to keep.
    }
};

const range = ref(remembered("range", props.defaultRange, RANGES.map((r) => r.value)));
const metric = ref(remembered("metric", "visitors", Object.keys(METRICS)));
const sourcesTab = ref("channels");

const report = ref(null);
const reportError = ref(null);
const reportLoading = ref(false);

const events = ref(null);
const eventsError = ref(null);
const eventsLoading = ref(false);

const live = ref(null);
const eventsPanel = ref(null);
const tick = ref(Date.now());

let controller;
let liveTimer;
let clockTimer;

async function getJson(url, params = {}, signal) {
    const query = new URLSearchParams(params).toString();
    const response = await fetch(query ? `${url}?${query}` : url, {
        credentials: "same-origin",
        headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" },
        signal,
    });
    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
        const error = new Error(data.message || `Analytics couldn't be loaded (${response.status}).`);
        error.code = data.error;
        throw error;
    }

    return data;
}

async function load() {
    controller?.abort();
    controller = new AbortController();
    const { signal } = controller;
    const params = { range: range.value };

    reportLoading.value = true;
    eventsLoading.value = true;
    reportError.value = null;
    eventsError.value = null;
    eventsPanel.value?.reset();

    getJson(props.urls.report, params, signal)
        .then((data) => {
            report.value = data;
        })
        .catch((error) => {
            if (error.name !== "AbortError") reportError.value = error;
        })
        .finally(() => {
            if (!signal.aborted) reportLoading.value = false;
        });

    getJson(props.urls.events, params, signal)
        .then((data) => {
            events.value = data;
        })
        .catch((error) => {
            if (error.name !== "AbortError") eventsError.value = error.message;
        })
        .finally(() => {
            if (!signal.aborted) eventsLoading.value = false;
        });
}

async function loadLive() {
    if (document.visibilityState !== "visible") return;

    try {
        live.value = await getJson(props.urls.live);
    } catch {
        // The count is a nicety; the rest of the widget doesn't depend on it.
    }
}

const loadEventPages = (name) => getJson(props.urls.event, { range: range.value, name });

watch(range, (value) => {
    remember("range", value);
    load();
});

watch(metric, (value) => remember("metric", value));

const onVisibility = () => document.visibilityState === "visible" && loadLive();

onMounted(() => {
    load();
    loadLive();
    liveTimer = window.setInterval(loadLive, 60_000);
    clockTimer = window.setInterval(() => (tick.value = Date.now()), 30_000);
    document.addEventListener("visibilitychange", onVisibility);
});

onBeforeUnmount(() => {
    controller?.abort();
    window.clearInterval(liveTimer);
    window.clearInterval(clockTimer);
    document.removeEventListener("visibilitychange", onVisibility);
});

const rangeInfo = computed(() => RANGES.find((r) => r.value === range.value) ?? RANGES[2]);

const tiles = computed(() =>
    Object.entries(METRICS).map(([key, m]) => {
        const current = report.value?.totals?.[key] ?? 0;
        const previous = report.value?.previous?.[key] ?? 0;

        return {
            key,
            label: m.label,
            help: m.help,
            value: m.format(current),
            full: key === "visitors" || key === "pageviews" ? formatFull(current) : m.format(current),
            previous: m.format(previous),
            delta: report.value ? delta(key, current, previous) : null,
            spark: sparkline(report.value?.series ?? [], key),
        };
    })
);

/** A 100×28 polyline for a tile; flat when there's nothing to show. */
function sparkline(series, key) {
    const values = series.map((point) => Number(point[key]) || 0);
    if (values.length < 2) return null;

    const max = Math.max(...values, 1);

    return values.map((value, index) => `${((index / (values.length - 1)) * 100).toFixed(2)},${(26 - (value / max) * 24).toFixed(2)}`).join(" ");
}

const pages = computed(() =>
    (report.value?.pages ?? []).map((page) => ({
        key: page.path,
        label: page.path,
        value: page.pageviews,
        href: `${props.siteUrl}${page.path}`,
        page,
    }))
);

const channels = computed(() => (report.value?.sources ?? []).map((source) => ({ key: source.name, label: source.name, value: source.visitors, source })));

const referrers = computed(() =>
    (report.value?.referrers ?? []).map((referrer) => ({
        key: referrer.host,
        label: referrer.host,
        value: referrer.visitors,
        href: `https://${referrer.host}`,
        referrer,
    }))
);

const campaigns = computed(() =>
    (report.value?.campaigns ?? []).map((campaign, index) => ({
        key: `${campaign.source}|${campaign.medium}|${campaign.campaign}|${index}`,
        label: campaign.campaign || campaign.source,
        value: campaign.visitors,
        campaign,
    }))
);

const countries = computed(() =>
    (report.value?.countries ?? []).map((country) => ({
        key: country.name,
        label: country.name === "Other" ? "Other countries" : countryName(country.name),
        flag: country.name === "Other" ? "🌐" : countryFlag(country.name),
        value: country.visitors,
    }))
);

const devices = computed(() =>
    (report.value?.devices ?? []).map((device, index) => ({
        name: device.name,
        visitors: device.visitors,
        color: DEVICE_COLORS[device.name] ?? `var(--fa-cat-${Math.min(4, index + 1)})`,
    }))
);

const browsers = computed(() => (report.value?.browsers ?? []).map((browser) => ({ key: browser.name, label: browser.name, value: browser.visitors })));

const audienceTotal = computed(() => report.value?.totals?.visitors || null);

const hasTraffic = computed(() => (report.value?.totals?.pageviews ?? 0) > 0);

const staleText = computed(() => {
    if (!report.value?.stale) return null;

    const reason = report.value.staleReason === "error" ? "Fathom couldn't be reached" : "Fathom's hourly limit was reached";
    return `Showing figures from ${timeAgo(report.value.generatedAt)} — ${reason}. They'll refresh on their own.`;
});

const updated = computed(() => {
    tick.value;
    return report.value?.generatedAt ? timeAgo(report.value.generatedAt) : null;
});

const deltaIcon = (d) => (d.direction === "up" ? "arrow-up" : d.direction === "down" ? "arrow-down" : null);
</script>

<template>
    <Widget :title="title" icon="chart-monitoring-indicator" class="fa-widget fa-tokens">
        <template #actions>
            <div class="fa-actions">
                <Popover v-if="live" side="bottom" align="end" :offset="8" class="fa-popover">
                    <template #trigger>
                        <button type="button" class="fa-live" :class="{ 'is-on': live.total > 0 }" :aria-label="`${live.total} people on the site now`">
                            <span class="fa-live__dot" />
                            <span class="fa-live__count">{{ formatFull(live.total) }}</span>
                            <span class="fa-live__label">on site now</span>
                        </button>
                    </template>
                    <div class="fa-livecard fa-tokens">
                        <p class="fa-livecard__title"><span class="fa-live__dot" :class="{ 'is-on': live.total > 0 }" /> {{ formatFull(live.total) }} {{ live.total === 1 ? "person" : "people" }} on the site right now</p>
                        <template v-if="live.pages.length">
                            <p class="fa-livecard__heading">Looking at</p>
                            <ul>
                                <li v-for="page in live.pages" :key="page.path">
                                    <a :href="`${siteUrl}${page.path}`" target="_blank" rel="noopener">{{ page.path }}</a>
                                    <span>{{ formatFull(page.total) }}</span>
                                </li>
                            </ul>
                        </template>
                        <template v-if="live.referrers.length">
                            <p class="fa-livecard__heading">Arrived from</p>
                            <ul>
                                <li v-for="referrer in live.referrers" :key="referrer.host">
                                    <span class="fa-livecard__host">{{ referrer.host }}</span>
                                    <span>{{ formatFull(referrer.total) }}</span>
                                </li>
                            </ul>
                        </template>
                        <p v-if="!live.total" class="fa-livecard__empty">Quiet right now. This updates every minute while the dashboard is open.</p>
                    </div>
                </Popover>

                <ToggleGroup v-model="range" size="sm" required aria-label="Date range">
                    <ToggleItem v-for="r in RANGES" :key="r.value" :value="r.value" :label="r.label" :aria-label="`Show ${r.long}`" />
                </ToggleGroup>

                <a :href="settingsUrl" class="fa-iconbtn" title="Analytics settings (Globals → Integrations)" aria-label="Analytics settings">
                    <Icon name="cog" />
                </a>
            </div>
        </template>

        <div class="fa-body" :class="{ 'is-refreshing': reportLoading && report }">
            <div v-if="reportLoading && report" class="fa-progress" aria-hidden="true" />

            <!-- Couldn't load, and nothing to show instead -->
            <div v-if="reportError && !report" class="fa-state">
                <Icon :name="['not_linked', 'not_connected'].includes(reportError.code) ? 'info' : 'warning-diamond'" class="fa-state__icon" />
                <p class="fa-state__title">
                    {{ ['not_linked', 'not_connected'].includes(reportError.code) ? "Analytics aren't connected yet" : "Analytics couldn't be loaded" }}
                </p>
                <p class="fa-state__text">{{ reportError.message }}</p>
                <button type="button" class="fa-button" @click="load">Try again</button>
            </div>

            <!-- First load -->
            <div v-else-if="!report" class="fa-skeleton-page" aria-busy="true" aria-label="Loading analytics">
                <div class="fa-tiles">
                    <div v-for="n in 4" :key="n" class="fa-tile is-skeleton">
                        <span class="fa-skeleton" style="width: 45%" />
                        <span class="fa-skeleton fa-skeleton--big" style="width: 60%" />
                    </div>
                </div>
                <div class="fa-skeleton fa-skeleton--chart" />
            </div>

            <template v-else>
                <div class="fa-tiles" role="group" aria-label="Choose what the chart shows">
                    <button
                        v-for="tile in tiles"
                        :key="tile.key"
                        type="button"
                        class="fa-tile"
                        :class="{ 'is-on': metric === tile.key }"
                        :aria-pressed="metric === tile.key"
                        :title="`${tile.help} Previous period: ${tile.previous}.`"
                        @click="metric = tile.key"
                    >
                        <span class="fa-tile__label">{{ tile.label }}</span>
                        <span class="fa-tile__value" :title="tile.full">{{ tile.value }}</span>
                        <span class="fa-tile__meta">
                            <span
                                v-if="tile.delta"
                                class="fa-delta"
                                :class="{ 'is-good': tile.delta.good === true, 'is-bad': tile.delta.good === false }"
                                :title="tile.delta.title"
                            >
                                <Icon v-if="deltaIcon(tile.delta)" :name="deltaIcon(tile.delta)" class="fa-delta__icon" />
                                {{ tile.delta.text }}
                            </span>
                            <span class="fa-tile__vs">vs {{ tile.previous }}</span>
                        </span>
                        <svg v-if="tile.spark" class="fa-tile__spark" viewBox="0 0 100 28" preserveAspectRatio="none" aria-hidden="true">
                            <polyline :points="tile.spark" />
                        </svg>
                    </button>
                </div>

                <section class="fa-panel fa-panel--chart">
                    <header class="fa-panel__head">
                        <div>
                            <h3 class="fa-panel__title">{{ METRICS[metric].label }}</h3>
                            <p class="fa-panel__sub">{{ report.range.label }} · by {{ report.range.grouping }}</p>
                        </div>
                    </header>
                    <TrendChart :series="report.series" :metric="metric" :grouping="report.range.grouping" :height="240" />
                    <p v-if="!hasTraffic" class="fa-overlay-note">No visits recorded in this period yet. Fathom starts counting as soon as the site's tracking script is live.</p>
                </section>

                <div class="fa-grid">
                    <section class="fa-panel">
                        <header class="fa-panel__head">
                            <h3 class="fa-panel__title">Top pages</h3>
                        </header>
                        <BarList :rows="pages" column-label="Page" value-label="Views" :total="report.totals.pageviews" empty="No page views in this period.">
                            <template #label="{ row }">
                                <span class="fa-path">{{ row.label }}</span>
                                <Icon name="external-link" class="fa-hover-icon" />
                            </template>
                            <template #tip="{ row, share }">
                                <p class="fa-tooltip__title">{{ row.label }}</p>
                                <dl>
                                    <div class="fa-tooltip__row is-lead"><dt><span class="fa-tooltip__key" />Views</dt><dd>{{ formatFull(row.page.pageviews) }}</dd></div>
                                    <div class="fa-tooltip__row"><dt>Visitors</dt><dd>{{ formatFull(row.page.visitors) }}</dd></div>
                                    <div class="fa-tooltip__row"><dt>Avg time</dt><dd>{{ formatDuration(row.page.avgDuration) }}</dd></div>
                                    <div class="fa-tooltip__row"><dt>Bounce rate</dt><dd>{{ Math.round(row.page.bounceRate) }}%</dd></div>
                                    <div class="fa-tooltip__row"><dt>Share of views</dt><dd>{{ share }}%</dd></div>
                                </dl>
                            </template>
                        </BarList>
                    </section>

                    <section class="fa-panel">
                        <header class="fa-panel__head">
                            <h3 class="fa-panel__title">Where visitors came from</h3>
                            <div class="fa-tabs" role="tablist" aria-label="Traffic sources">
                                <button
                                    v-for="tab in [{ k: 'channels', t: 'Channels' }, { k: 'referrers', t: 'Referrers' }, { k: 'campaigns', t: 'Campaigns' }]"
                                    :key="tab.k"
                                    type="button"
                                    role="tab"
                                    class="fa-tab"
                                    :class="{ 'is-on': sourcesTab === tab.k }"
                                    :aria-selected="sourcesTab === tab.k"
                                    @click="sourcesTab = tab.k"
                                >
                                    {{ tab.t }}
                                </button>
                            </div>
                        </header>

                        <BarList v-if="sourcesTab === 'channels'" :rows="channels" column-label="Channel" :total="audienceTotal" empty="No visits in this period.">
                            <template #tip="{ row, share }">
                                <p class="fa-tooltip__title">{{ row.label }}</p>
                                <dl>
                                    <div class="fa-tooltip__row is-lead"><dt><span class="fa-tooltip__key" />Visitors</dt><dd>{{ formatFull(row.source.visitors) }}</dd></div>
                                    <div class="fa-tooltip__row"><dt>Views</dt><dd>{{ formatFull(row.source.pageviews) }}</dd></div>
                                    <div class="fa-tooltip__row"><dt>Share</dt><dd>{{ share }}%</dd></div>
                                </dl>
                            </template>
                        </BarList>

                        <BarList v-else-if="sourcesTab === 'referrers'" :rows="referrers" column-label="Site" :total="audienceTotal" empty="No referring sites in this period — visitors arrived directly.">
                            <template #label="{ row }">
                                <span class="fa-path">{{ row.label }}</span>
                                <Icon name="external-link" class="fa-hover-icon" />
                            </template>
                            <template #tip="{ row, share }">
                                <p class="fa-tooltip__title">{{ row.label }}</p>
                                <dl>
                                    <div class="fa-tooltip__row is-lead"><dt><span class="fa-tooltip__key" />Visitors</dt><dd>{{ formatFull(row.referrer.visitors) }}</dd></div>
                                    <div class="fa-tooltip__row"><dt>Views</dt><dd>{{ formatFull(row.referrer.pageviews) }}</dd></div>
                                    <div class="fa-tooltip__row"><dt>Channel</dt><dd>{{ row.referrer.source || "Referral" }}</dd></div>
                                    <div class="fa-tooltip__row"><dt>Share of visitors</dt><dd>{{ share }}%</dd></div>
                                </dl>
                            </template>
                        </BarList>

                        <BarList v-else :rows="campaigns" column-label="Campaign" :total="audienceTotal" empty="No tagged campaigns (utm_ links) in this period.">
                            <template #label="{ row }">
                                <span class="fa-path">{{ row.label }}</span>
                                <span class="fa-pill">{{ row.campaign.source || "—" }}</span>
                            </template>
                            <template #tip="{ row, share }">
                                <p class="fa-tooltip__title">{{ row.label }}</p>
                                <dl>
                                    <div class="fa-tooltip__row is-lead"><dt><span class="fa-tooltip__key" />Visitors</dt><dd>{{ formatFull(row.campaign.visitors) }}</dd></div>
                                    <div class="fa-tooltip__row"><dt>Views</dt><dd>{{ formatFull(row.campaign.pageviews) }}</dd></div>
                                    <div class="fa-tooltip__row"><dt>Source</dt><dd>{{ row.campaign.source || "—" }}</dd></div>
                                    <div class="fa-tooltip__row"><dt>Medium</dt><dd>{{ row.campaign.medium || "—" }}</dd></div>
                                    <div class="fa-tooltip__row"><dt>Share of visitors</dt><dd>{{ share }}%</dd></div>
                                </dl>
                            </template>
                        </BarList>
                    </section>

                    <section class="fa-panel">
                        <header class="fa-panel__head">
                            <h3 class="fa-panel__title">Locations</h3>
                        </header>
                        <BarList :rows="countries" column-label="Country" :total="audienceTotal" empty="No visits in this period.">
                            <template #label="{ row }">
                                <span class="fa-flag" aria-hidden="true">{{ row.flag }}</span>
                                <span class="fa-path">{{ row.label }}</span>
                            </template>
                            <template #tip="{ row, share }">
                                <p class="fa-tooltip__title">{{ row.flag }} {{ row.label }}</p>
                                <dl>
                                    <div class="fa-tooltip__row is-lead"><dt><span class="fa-tooltip__key" />Visitors</dt><dd>{{ formatFull(row.value) }}</dd></div>
                                    <div class="fa-tooltip__row"><dt>Share</dt><dd>{{ share }}%</dd></div>
                                </dl>
                            </template>
                        </BarList>
                    </section>

                    <section class="fa-panel">
                        <header class="fa-panel__head">
                            <h3 class="fa-panel__title">Devices &amp; browsers</h3>
                        </header>
                        <SegmentBar v-if="devices.length" :parts="devices" label="Visitors by device" />
                        <p v-else class="fa-empty">No visits in this period.</p>
                        <div class="fa-spacer" />
                        <BarList :rows="browsers" column-label="Browser" :total="audienceTotal" empty="">
                            <template #tip="{ row, share }">
                                <p class="fa-tooltip__title">{{ row.label }}</p>
                                <dl>
                                    <div class="fa-tooltip__row is-lead"><dt><span class="fa-tooltip__key" />Visitors</dt><dd>{{ formatFull(row.value) }}</dd></div>
                                    <div class="fa-tooltip__row"><dt>Share</dt><dd>{{ share }}%</dd></div>
                                </dl>
                            </template>
                        </BarList>
                    </section>
                </div>
            </template>

            <EventsPanel
                v-if="report || eventsLoading || events"
                ref="eventsPanel"
                :data="events"
                :loading="eventsLoading"
                :error="eventsError"
                :events-on="eventsOn"
                :settings-url="settingsUrl"
                :site-url="siteUrl"
                :load-pages="loadEventPages"
            />

            <footer v-if="report" class="fa-foot">
                <p v-if="staleText" class="fa-stale"><Icon name="time-clock" class="fa-stale__icon" />{{ staleText }}</p>
                <p class="fa-foot__meta">
                    Fathom Analytics · {{ rangeInfo.long }} · times in {{ report.timezone }}<template v-if="updated && !report.stale"> · updated {{ updated }}</template>
                </p>
            </footer>
        </div>
    </Widget>
</template>

<style>
/*
 * Colour tokens for the analytics widget and anything it portals out (the
 * event drill-down, the live card). Series and status hues are the validated
 * dataviz reference palette; ink and surfaces follow the CP's own greys.
 */
.fa-tokens {
    --fa-surface: var(--theme-color-content-bg, #fff);
    --fa-ink: var(--theme-color-gray-900, #18181b);
    --fa-ink-2: var(--theme-color-gray-600, #52525b);
    --fa-muted: var(--theme-color-gray-500, #71717a);
    --fa-hairline: var(--theme-color-gray-200, #e4e4e7);
    --fa-grid: var(--theme-color-gray-150, #ececee);
    --fa-axis: var(--theme-color-gray-300, #d4d4d8);
    --fa-hover: var(--theme-color-gray-50, #fafafa);
    --fa-focus: var(--theme-color-focus-outline, #2a78d6);
    --fa-series: #2a78d6;
    --fa-wash: color-mix(in oklab, var(--fa-series) 9%, transparent);
    --fa-wash-strong: color-mix(in oklab, var(--fa-series) 17%, transparent);
    --fa-cat-1: #2a78d6;
    --fa-cat-2: #eb6834;
    --fa-cat-3: #1baf7a;
    --fa-cat-4: #eda100;
    --fa-good: #006300;
    --fa-bad: #c42f2f;
    --fa-live: #0ca30c;
}

.dark .fa-tokens {
    --fa-surface: var(--theme-color-gray-900, #18181b);
    --fa-ink: var(--theme-color-gray-100, #f4f4f5);
    --fa-ink-2: var(--theme-color-gray-300, #d4d4d8);
    --fa-muted: var(--theme-color-gray-400, #a1a1aa);
    --fa-hairline: var(--theme-color-gray-700, #3f3f46);
    --fa-grid: var(--theme-color-gray-800, #27272a);
    --fa-axis: var(--theme-color-gray-700, #3f3f46);
    --fa-hover: var(--theme-color-gray-850, #1f1f23);
    --fa-series: #3987e5;
    --fa-wash: color-mix(in oklab, var(--fa-series) 14%, transparent);
    --fa-wash-strong: color-mix(in oklab, var(--fa-series) 24%, transparent);
    --fa-cat-1: #3987e5;
    --fa-cat-2: #d95926;
    --fa-cat-3: #199e70;
    --fa-cat-4: #c98500;
    --fa-good: #0ca30c;
    --fa-bad: #e66767;
}

.fa-tokens .fa-tooltip {
    padding: 10px 12px;
    border-radius: 10px;
    color: var(--fa-ink);
    background: var(--fa-surface);
    box-shadow:
        0 0 0 1px var(--fa-hairline),
        0 12px 32px -8px rgb(0 0 0 / 0.22);
    font-size: 12px;
    white-space: nowrap;
}

.fa-tokens .fa-tooltip__title {
    margin-bottom: 6px;
    font-weight: 600;
    color: var(--fa-ink);
    white-space: normal;
    overflow-wrap: anywhere;
}

.fa-tokens .fa-tooltip__row {
    display: flex;
    justify-content: space-between;
    gap: 18px;
    padding: 1px 0;
    color: var(--fa-ink-2);
}

.fa-tokens .fa-tooltip__row dt {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.fa-tokens .fa-tooltip__row dd {
    font-variant-numeric: tabular-nums;
}

.fa-tokens .fa-tooltip__row.is-lead dd {
    font-size: 14px;
    font-weight: 700;
    color: var(--fa-ink);
}

.fa-tokens .fa-tooltip__key {
    display: inline-block;
    width: 12px;
    height: 2px;
    border-radius: 2px;
    background: var(--fa-series);
}

.fa-tokens .fa-sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    overflow: hidden;
    clip: rect(0 0 0 0);
    white-space: nowrap;
}

.fa-tokens .fa-panel {
    position: relative;
    min-width: 0;
    padding: 16px;
    border-radius: 12px;
    background: var(--fa-surface);
    box-shadow: 0 0 0 1px var(--fa-hairline);
}

.fa-tokens .fa-panel__head {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 12px;
}

.fa-tokens .fa-panel__title {
    font-size: 14px;
    font-weight: 600;
    color: var(--fa-ink);
}

.fa-tokens .fa-panel__sub {
    margin-top: 2px;
    font-size: 12px;
    color: var(--fa-muted);
}

.fa-tokens .fa-empty {
    padding: 14px 10px;
    font-size: 13px;
    color: var(--fa-muted);
}

.fa-tokens .fa-skeleton {
    display: block;
    height: 12px;
    border-radius: 6px;
    background: linear-gradient(90deg, var(--fa-grid) 0%, var(--fa-hover) 50%, var(--fa-grid) 100%);
    background-size: 200% 100%;
    animation: fa-shimmer 1.4s ease-in-out infinite;
}

.fa-tokens .fa-skeleton-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 8px 10px;
}

/* Statamic's popover is a fixed w-64; ours size to their content. */
[data-ui-popover-content].fa-popover {
    width: auto;
    max-width: min(380px, calc(100vw - 32px));
}

.fa-tokens .fa-hover-icon {
    flex: none;
    width: 12px;
    height: 12px;
    color: var(--fa-muted);
    opacity: 0;
    transition: opacity 150ms;
}

.fa-tokens .fa-bar.is-active .fa-hover-icon,
.fa-tokens .fa-bar:focus-visible .fa-hover-icon {
    opacity: 1;
}

@keyframes fa-shimmer {
    from {
        background-position: 100% 0;
    }
    to {
        background-position: -100% 0;
    }
}

@media (prefers-reduced-motion: reduce) {
    .fa-tokens .fa-skeleton {
        animation: none;
    }
}
</style>

<style scoped>
.fa-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
}

.fa-iconbtn {
    display: grid;
    place-items: center;
    width: 30px;
    height: 30px;
    border-radius: 8px;
    color: var(--fa-muted);
    transition:
        background-color 150ms,
        color 150ms;
}

.fa-iconbtn:hover {
    color: var(--fa-ink);
    background: var(--fa-hover);
}

.fa-iconbtn :deep(svg) {
    width: 16px;
    height: 16px;
}

.fa-live {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    height: 30px;
    padding: 0 11px;
    border-radius: 999px;
    font-size: 12px;
    color: var(--fa-ink-2);
    box-shadow: 0 0 0 1px var(--fa-hairline);
    transition: background-color 150ms;
}

.fa-live:hover {
    background: var(--fa-hover);
}

.fa-live__count {
    font-weight: 700;
    color: var(--fa-ink);
    font-variant-numeric: tabular-nums;
}

.fa-live__dot {
    position: relative;
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--fa-axis);
}

.fa-live.is-on .fa-live__dot,
.fa-live__dot.is-on {
    background: var(--fa-live);
}

.fa-live.is-on .fa-live__dot::after {
    position: absolute;
    inset: -3px;
    border-radius: 50%;
    background: var(--fa-live);
    opacity: 0.35;
    animation: fa-pulse 2s ease-out infinite;
    content: "";
}

@keyframes fa-pulse {
    0% {
        transform: scale(0.6);
        opacity: 0.5;
    }
    100% {
        transform: scale(1.8);
        opacity: 0;
    }
}

@media (prefers-reduced-motion: reduce) {
    .fa-live.is-on .fa-live__dot::after {
        animation: none;
    }
}

.fa-livecard {
    width: min(300px, 80vw);
    padding: 4px;
    font-size: 13px;
    color: var(--fa-ink);
}

.fa-livecard__title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
}

.fa-livecard__heading {
    margin: 12px 0 4px;
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.02em;
    color: var(--fa-muted);
    text-transform: uppercase;
}

.fa-livecard li {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    padding: 3px 0;
}

.fa-livecard a,
.fa-livecard__host {
    overflow: hidden;
    color: var(--fa-ink);
    white-space: nowrap;
    text-overflow: ellipsis;
}

.fa-livecard a:hover {
    text-decoration: underline;
}

.fa-livecard li span:last-child {
    font-weight: 600;
    font-variant-numeric: tabular-nums;
}

.fa-livecard__empty {
    margin-top: 8px;
    color: var(--fa-muted);
}

.fa-body {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 16px;
    padding: 16px 18px 14px;
}

.fa-body.is-refreshing .fa-tiles,
.fa-body.is-refreshing .fa-panel--chart,
.fa-body.is-refreshing .fa-grid {
    opacity: 0.55;
    transition: opacity 200ms;
}

.fa-progress {
    position: absolute;
    top: 0;
    right: 18px;
    left: 18px;
    height: 2px;
    overflow: hidden;
    border-radius: 2px;
}

.fa-progress::after {
    position: absolute;
    inset: 0;
    width: 35%;
    border-radius: 2px;
    background: var(--fa-series);
    animation: fa-progress 1.1s ease-in-out infinite;
    content: "";
}

@keyframes fa-progress {
    from {
        transform: translateX(-100%);
    }
    to {
        transform: translateX(290%);
    }
}

.fa-tiles {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
}

@media (max-width: 900px) {
    .fa-tiles {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

.fa-tile {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
    padding: 14px 16px 16px;
    overflow: hidden;
    border-radius: 12px;
    text-align: left;
    background: var(--fa-surface);
    box-shadow: 0 0 0 1px var(--fa-hairline);
    transition:
        box-shadow 150ms,
        background-color 150ms,
        transform 150ms;
}

.fa-tile:not(.is-skeleton):hover {
    background: var(--fa-hover);
}

.fa-tile.is-on {
    box-shadow:
        0 0 0 1.5px var(--fa-series),
        0 8px 24px -14px var(--fa-series);
}

.fa-tile:focus-visible {
    outline: 2px solid var(--fa-focus);
    outline-offset: 2px;
}

.fa-tile.is-skeleton {
    gap: 12px;
}

.fa-tile__label {
    font-size: 12px;
    font-weight: 500;
    color: var(--fa-ink-2);
}

.fa-tile__value {
    font-size: 26px;
    font-weight: 650;
    line-height: 1.15;
    letter-spacing: -0.01em;
    color: var(--fa-ink);
}

.fa-tile__meta {
    position: relative;
    z-index: 1;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    font-size: 12px;
}

.fa-tile__vs {
    color: var(--fa-muted);
    font-variant-numeric: tabular-nums;
}

.fa-tile__spark {
    position: absolute;
    right: 12px;
    bottom: 12px;
    width: 34%;
    height: 28px;
    opacity: 0.45;
    transition: opacity 150ms;
}

.fa-tile.is-on .fa-tile__spark {
    opacity: 1;
}

.fa-tile__spark polyline {
    fill: none;
    stroke: var(--fa-muted);
    stroke-width: 1.5;
    stroke-linejoin: round;
    stroke-linecap: round;
    vector-effect: non-scaling-stroke;
}

.fa-tile.is-on .fa-tile__spark polyline {
    stroke: var(--fa-series);
}

.fa-delta {
    display: inline-flex;
    align-items: center;
    gap: 2px;
    padding: 1px 7px 1px 5px;
    border-radius: 999px;
    font-weight: 600;
    color: var(--fa-ink-2);
    background: var(--fa-hover);
    box-shadow: 0 0 0 1px var(--fa-hairline);
    font-variant-numeric: tabular-nums;
}

.fa-delta.is-good {
    color: var(--fa-good);
    background: color-mix(in oklab, var(--fa-good) 10%, transparent);
    box-shadow: none;
}

.fa-delta.is-bad {
    color: var(--fa-bad);
    background: color-mix(in oklab, var(--fa-bad) 10%, transparent);
    box-shadow: none;
}

.fa-delta__icon {
    width: 12px;
    height: 12px;
}

.fa-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

@media (max-width: 1100px) {
    .fa-grid {
        grid-template-columns: minmax(0, 1fr);
    }
}

.fa-panel--chart {
    padding-bottom: 8px;
}

.fa-overlay-note {
    position: absolute;
    top: 50%;
    left: 50%;
    max-width: 360px;
    padding: 10px 14px;
    border-radius: 10px;
    font-size: 13px;
    text-align: center;
    color: var(--fa-ink-2);
    background: var(--fa-surface);
    box-shadow: 0 0 0 1px var(--fa-hairline);
    transform: translate(-50%, -20%);
}

.fa-tabs {
    display: inline-flex;
    gap: 2px;
    padding: 2px;
    border-radius: 9px;
    background: var(--fa-hover);
    box-shadow: inset 0 0 0 1px var(--fa-hairline);
}

.fa-tab {
    height: 26px;
    padding: 0 10px;
    border-radius: 7px;
    font-size: 12px;
    color: var(--fa-ink-2);
    transition:
        background-color 150ms,
        color 150ms;
}

.fa-tab.is-on {
    color: var(--fa-ink);
    background: var(--fa-surface);
    box-shadow: 0 1px 2px rgb(0 0 0 / 0.08), 0 0 0 1px var(--fa-hairline);
}

.fa-tab:focus-visible {
    outline: 2px solid var(--fa-focus);
    outline-offset: 1px;
}

.fa-path {
    overflow: hidden;
    text-overflow: ellipsis;
}

.fa-flag {
    flex: none;
    font-size: 15px;
    line-height: 1;
}

.fa-pill {
    flex: none;
    padding: 0 7px;
    border-radius: 999px;
    font-size: 11px;
    color: var(--fa-ink-2);
    box-shadow: 0 0 0 1px var(--fa-hairline);
}

.fa-spacer {
    height: 18px;
}

.fa-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    padding: 40px 16px;
    text-align: center;
}

.fa-state__icon {
    width: 22px;
    height: 22px;
    color: var(--fa-muted);
}

.fa-state__title {
    font-size: 15px;
    font-weight: 600;
    color: var(--fa-ink);
}

.fa-state__text {
    max-width: 420px;
    font-size: 13px;
    color: var(--fa-ink-2);
}

.fa-button {
    margin-top: 8px;
    height: 32px;
    padding: 0 14px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    color: var(--fa-ink);
    box-shadow: 0 0 0 1px var(--fa-hairline);
}

.fa-button:hover {
    background: var(--fa-hover);
}

.fa-skeleton-page {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.fa-skeleton--big {
    height: 26px;
}

.fa-skeleton--chart {
    height: 260px;
    border-radius: 12px;
}

:deep(.fa-notice) {
    display: flex;
    gap: 10px;
    padding: 10px 12px;
    margin-bottom: 12px;
    border-radius: 10px;
    font-size: 13px;
    color: var(--fa-ink-2);
    background: var(--fa-hover);
    box-shadow: inset 0 0 0 1px var(--fa-hairline);
}

:deep(.fa-notice a) {
    color: var(--fa-ink);
    text-decoration: underline;
}

:deep(.fa-notice__icon) {
    flex: none;
    width: 16px;
    height: 16px;
    margin-top: 1px;
}

:deep(.fa-footnote) {
    margin-top: 10px;
    font-size: 12px;
    color: var(--fa-muted);
}

.fa-foot {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding-top: 2px;
}

.fa-stale {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border-radius: 10px;
    font-size: 12px;
    color: var(--fa-ink-2);
    background: color-mix(in oklab, #fab219 12%, transparent);
    box-shadow: inset 0 0 0 1px color-mix(in oklab, #fab219 40%, transparent);
}

.fa-stale__icon {
    flex: none;
    width: 14px;
    height: 14px;
}

.fa-foot__meta {
    font-size: 11px;
    color: var(--fa-muted);
}
</style>
