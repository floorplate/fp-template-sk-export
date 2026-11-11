<script setup>
import { computed, reactive, ref } from "vue";
import { Icon, Popover } from "@statamic/cms/ui";
import { EVENT_KINDS, formatCount, formatFull, parseEvent } from "./format";

/**
 * The clicks the kit tracks (fathomEvents.js), ranked. Filter by kind; open a
 * row to see the pages it happened on, fetched when first opened.
 */
const props = defineProps({
    data: { type: Object, default: null }, // { events: [{ name, conversions, uniqueConversions }], partial, tracked }
    loading: Boolean,
    error: { type: String, default: null },
    eventsOn: Boolean,
    settingsUrl: { type: String, required: true },
    siteUrl: { type: String, required: true },
    loadPages: { type: Function, required: true }, // (name) => Promise<{ pages }>
});

const kind = ref("all");
const pages = reactive({});

const events = computed(() => (props.data?.events ?? []).map((event) => ({ ...event, ...parseEvent(event.name) })));

const kinds = computed(() => {
    const counts = {};
    for (const event of events.value) counts[event.kind] = (counts[event.kind] ?? 0) + event.conversions;

    return Object.entries(counts)
        .sort((a, b) => b[1] - a[1])
        .map(([key, total]) => ({ key, total, ...(EVENT_KINDS[key] ?? EVENT_KINDS.Event) }));
});

const visible = computed(() => (kind.value === "all" ? events.value : events.value.filter((event) => event.kind === kind.value)));
const max = computed(() => Math.max(1, ...visible.value.map((event) => event.conversions)));
const total = computed(() => events.value.reduce((sum, event) => sum + event.conversions, 0));

const iconFor = (event) => (EVENT_KINDS[event.kind] ?? EVENT_KINDS.Event).icon;

async function open(event, isOpen) {
    if (!isOpen || pages[event.name]) return;

    pages[event.name] = { loading: true };

    try {
        const result = await props.loadPages(event.name);
        pages[event.name] = { pages: result.pages ?? [] };
    } catch (error) {
        pages[event.name] = { error: error.message || "Couldn't load the pages." };
    }
}

function reset() {
    for (const key of Object.keys(pages)) delete pages[key];
}

defineExpose({ reset });
</script>

<template>
    <section class="fa-panel fa-events" :class="{ 'is-loading': loading && data }">
        <header class="fa-panel__head">
            <div>
                <h3 class="fa-panel__title">Clicks &amp; conversions</h3>
                <p class="fa-panel__sub">
                    <template v-if="data && events.length">{{ formatFull(total) }} tracked {{ total === 1 ? "action" : "actions" }} — what was clicked, and in which section.</template>
                    <template v-else>Buttons, phone numbers, emails, downloads, links and forms.</template>
                </p>
            </div>
            <div v-if="kinds.length > 1" class="fa-chips" role="group" aria-label="Filter by kind">
                <button type="button" class="fa-chip" :class="{ 'is-on': kind === 'all' }" :aria-pressed="kind === 'all'" @click="kind = 'all'">All</button>
                <button
                    v-for="k in kinds"
                    :key="k.key"
                    type="button"
                    class="fa-chip"
                    :class="{ 'is-on': kind === k.key }"
                    :aria-pressed="kind === k.key"
                    @click="kind = kind === k.key ? 'all' : k.key"
                >
                    <Icon :name="k.icon" class="fa-chip__icon" />
                    {{ k.label }}
                    <span class="fa-chip__count">{{ formatCount(k.total) }}</span>
                </button>
            </div>
        </header>

        <div v-if="!eventsOn" class="fa-notice">
            <Icon name="info" class="fa-notice__icon" />
            <p>
                Click tracking is off for this site.
                <a :href="settingsUrl">Turn on “Track clicks as events”</a> in Globals → Integrations to see which buttons, numbers and forms people use.
            </p>
        </div>

        <div v-if="!data && loading" class="fa-skeleton-list">
            <span v-for="n in 4" :key="n" class="fa-skeleton" :style="{ width: `${90 - n * 12}%` }" />
        </div>

        <p v-else-if="error && !data" class="fa-empty">{{ error }}</p>

        <p v-else-if="data && !events.length && eventsOn" class="fa-empty">
            No clicks recorded in this period yet. They appear here as visitors use buttons, phone numbers, email links, downloads and forms.
        </p>

        <ul v-else-if="data" class="fa-events__list">
            <li v-for="event in visible" :key="event.name">
                <Popover side="bottom" align="start" :offset="6" class="fa-popover" @update:open="open(event, $event)">
                    <template #trigger>
                        <button type="button" class="fa-event">
                            <span class="fa-event__fill" :style="{ width: `${Math.max(1.5, (event.conversions / max) * 100)}%` }" />
                            <span class="fa-event__icon"><Icon :name="iconFor(event)" /></span>
                            <span class="fa-event__label">
                                <span class="fa-event__name">{{ event.label }}</span>
                                <span v-if="event.section" class="fa-event__section">{{ event.section }}</span>
                            </span>
                            <span class="fa-event__unique" :title="`${formatFull(event.uniqueConversions)} unique visitors`">{{ formatCount(event.uniqueConversions) }} people</span>
                            <span class="fa-event__value" :title="formatFull(event.conversions)">{{ formatCount(event.conversions) }}</span>
                        </button>
                    </template>

                    <div class="fa-drill fa-tokens">
                        <p class="fa-drill__kind">
                            <Icon :name="iconFor(event)" />
                            {{ (EVENT_KINDS[event.kind] ?? EVENT_KINDS.Event).label }}<template v-if="event.section"> · {{ event.section }}</template>
                        </p>
                        <p class="fa-drill__title">{{ event.label }}</p>
                        <dl class="fa-drill__stats">
                            <div>
                                <dt>Times</dt>
                                <dd>{{ formatFull(event.conversions) }}</dd>
                            </div>
                            <div>
                                <dt>People</dt>
                                <dd>{{ formatFull(event.uniqueConversions) }}</dd>
                            </div>
                        </dl>
                        <p class="fa-drill__heading">Where it happened</p>
                        <div v-if="pages[event.name]?.loading" class="fa-skeleton-list">
                            <span v-for="n in 3" :key="n" class="fa-skeleton" :style="{ width: `${92 - n * 18}%` }" />
                        </div>
                        <p v-else-if="pages[event.name]?.error" class="fa-empty">{{ pages[event.name].error }}</p>
                        <p v-else-if="pages[event.name] && !pages[event.name].pages.length" class="fa-empty">No page breakdown for this period.</p>
                        <ul v-else-if="pages[event.name]" class="fa-drill__pages">
                            <li v-for="page in pages[event.name].pages" :key="page.path">
                                <a :href="`${siteUrl}${page.path}`" target="_blank" rel="noopener">{{ page.path }}</a>
                                <span>{{ formatFull(page.conversions) }}</span>
                            </li>
                        </ul>
                        <p class="fa-drill__raw">Fathom event: “{{ event.name }}”</p>
                    </div>
                </Popover>
            </li>
        </ul>

        <p v-if="data?.partial" class="fa-footnote">
            Showing the first {{ data.checked }} of {{ data.tracked }} tracked event names to stay within Fathom's hourly limit.
        </p>
    </section>
</template>

<style scoped>
.fa-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.fa-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 28px;
    padding: 0 10px;
    border: 1px solid var(--fa-hairline);
    border-radius: 999px;
    font-size: 12px;
    color: var(--fa-ink-2);
    background: var(--fa-surface);
    transition:
        background-color 150ms,
        border-color 150ms,
        color 150ms;
}

.fa-chip:hover {
    background: var(--fa-hover);
}

.fa-chip.is-on {
    border-color: var(--fa-series);
    color: var(--fa-ink);
    background: var(--fa-wash);
}

.fa-chip:focus-visible {
    outline: 2px solid var(--fa-focus);
    outline-offset: 1px;
}

.fa-chip__icon {
    width: 13px;
    height: 13px;
}

.fa-chip__count {
    font-weight: 600;
    color: var(--fa-ink);
    font-variant-numeric: tabular-nums;
}

.fa-events__list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 4px 16px;
}

.fa-event {
    position: relative;
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    min-height: 40px;
    padding: 0 10px;
    border-radius: 8px;
    text-align: left;
    color: var(--fa-ink);
    isolation: isolate;
}

.fa-event:hover .fa-event__fill,
.fa-event[data-state="open"] .fa-event__fill {
    background: var(--fa-wash-strong);
}

.fa-event:focus-visible {
    outline: 2px solid var(--fa-focus);
    outline-offset: 1px;
}

.fa-event__fill {
    position: absolute;
    inset: 0 auto 0 0;
    z-index: -1;
    border-radius: 8px;
    background: var(--fa-wash);
    transition:
        width 600ms cubic-bezier(0.22, 1, 0.36, 1),
        background-color 150ms;
}

.fa-event__icon {
    display: grid;
    flex: none;
    place-items: center;
    width: 26px;
    height: 26px;
    border-radius: 7px;
    color: var(--fa-ink-2);
    background: var(--fa-surface);
    box-shadow: 0 0 0 1px var(--fa-hairline);
}

.fa-event__icon :deep(svg) {
    width: 14px;
    height: 14px;
}

.fa-event__label {
    display: flex;
    flex: 1;
    align-items: center;
    gap: 8px;
    min-width: 0;
}

.fa-event__name {
    overflow: hidden;
    font-size: 13px;
    font-weight: 500;
    white-space: nowrap;
    text-overflow: ellipsis;
}

.fa-event__section {
    flex: none;
    padding: 1px 7px;
    border-radius: 999px;
    font-size: 11px;
    color: var(--fa-ink-2);
    background: var(--fa-surface);
    box-shadow: 0 0 0 1px var(--fa-hairline);
}

.fa-event__unique {
    flex: none;
    font-size: 12px;
    color: var(--fa-muted);
    font-variant-numeric: tabular-nums;
}

.fa-event__value {
    flex: none;
    min-width: 3ch;
    font-size: 13px;
    font-weight: 600;
    text-align: right;
    font-variant-numeric: tabular-nums;
}

.fa-drill {
    width: min(340px, 80vw);
    padding: 4px;
}

.fa-drill__kind {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.02em;
    color: var(--fa-muted);
    text-transform: uppercase;
}

.fa-drill__kind :deep(svg) {
    width: 12px;
    height: 12px;
}

.fa-drill__title {
    margin-top: 4px;
    font-size: 15px;
    font-weight: 600;
    color: var(--fa-ink);
    overflow-wrap: anywhere;
}

.fa-drill__stats {
    display: flex;
    gap: 24px;
    margin: 12px 0;
}

.fa-drill__stats dt {
    font-size: 11px;
    color: var(--fa-muted);
}

.fa-drill__stats dd {
    font-size: 18px;
    font-weight: 600;
    color: var(--fa-ink);
    font-variant-numeric: tabular-nums;
}

.fa-drill__heading {
    padding-top: 10px;
    border-top: 1px solid var(--fa-hairline);
    margin-bottom: 6px;
    font-size: 12px;
    font-weight: 600;
    color: var(--fa-ink-2);
}

.fa-drill__pages li {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    padding: 4px 0;
    font-size: 13px;
}

.fa-drill__pages a {
    overflow: hidden;
    color: var(--fa-ink);
    white-space: nowrap;
    text-overflow: ellipsis;
}

.fa-drill__pages a:hover {
    text-decoration: underline;
}

.fa-drill__pages span {
    font-weight: 600;
    color: var(--fa-ink);
    font-variant-numeric: tabular-nums;
}

.fa-drill__raw {
    margin-top: 10px;
    font-size: 11px;
    color: var(--fa-muted);
    overflow-wrap: anywhere;
}
</style>
