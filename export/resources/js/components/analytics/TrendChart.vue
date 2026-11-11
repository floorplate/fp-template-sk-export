<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { bucketLabel, METRICS, niceTicks } from "./format";

/**
 * One metric over the period, as a soft area under a 2px line. A crosshair
 * snaps to the nearest bucket and the tooltip lists every metric for it, the
 * charted one first. Arrow keys walk the buckets; a hidden table carries the
 * same figures for screen readers.
 */
const props = defineProps({
    series: { type: Array, required: true }, // [{ date, visitors, pageviews, avgDuration, bounceRate }]
    metric: { type: String, default: "visitors" },
    grouping: { type: String, default: "day" },
    height: { type: Number, default: 240 },
    partialLast: { type: Boolean, default: true },
});

const PAD = { top: 16, right: 12, bottom: 28, left: 44 };

const root = ref(null);
const width = ref(640);
const active = ref(null);

let observer;

onMounted(() => {
    observer = new ResizeObserver(([entry]) => {
        width.value = Math.max(240, Math.round(entry.contentRect.width));
    });
    observer.observe(root.value);
});

onBeforeUnmount(() => observer?.disconnect());

const plotWidth = computed(() => width.value - PAD.left - PAD.right);
const plotHeight = computed(() => props.height - PAD.top - PAD.bottom);

const values = computed(() => props.series.map((point) => Number(point[props.metric]) || 0));

const ticks = computed(() => {
    const max = Math.max(...values.value, 0);
    return props.metric === "bounceRate" ? niceTicks(Math.min(100, Math.max(max, 10)), 4) : niceTicks(max, 4);
});

const yMax = computed(() => ticks.value.at(-1) || 1);

const x = (index) => {
    const n = props.series.length;
    return PAD.left + (n > 1 ? (index / (n - 1)) * plotWidth.value : plotWidth.value / 2);
};

const y = (value) => PAD.top + plotHeight.value - (value / yMax.value) * plotHeight.value;

const points = computed(() => values.value.map((value, index) => ({ x: x(index), y: y(value) })));

/** Monotone cubic (Fritsch–Carlson) segments: smooth, but never overshooting a value or dipping below zero. */
const curveSegments = computed(() => {
    const p = points.value;
    if (p.length < 2) return [];

    const n = p.length;
    const dx = [];
    const slopes = [];
    for (let i = 0; i < n - 1; i++) {
        dx[i] = p[i + 1].x - p[i].x;
        slopes[i] = (p[i + 1].y - p[i].y) / dx[i];
    }

    const tangents = [slopes[0]];
    for (let i = 1; i < n - 1; i++) {
        tangents[i] = slopes[i - 1] * slopes[i] <= 0 ? 0 : (slopes[i - 1] + slopes[i]) / 2;
    }
    tangents[n - 1] = slopes[n - 2];

    for (let i = 0; i < n - 1; i++) {
        if (slopes[i] === 0) {
            tangents[i] = tangents[i + 1] = 0;
            continue;
        }
        const a = tangents[i] / slopes[i];
        const b = tangents[i + 1] / slopes[i];
        const h = a * a + b * b;
        if (h > 9) {
            const t = 3 / Math.sqrt(h);
            tangents[i] = t * a * slopes[i];
            tangents[i + 1] = t * b * slopes[i];
        }
    }

    return p.slice(0, -1).map((point, i) => {
        const c1x = point.x + dx[i] / 3;
        const c1y = point.y + (tangents[i] * dx[i]) / 3;
        const c2x = p[i + 1].x - dx[i] / 3;
        const c2y = p[i + 1].y - (tangents[i + 1] * dx[i]) / 3;

        return ` C${c1x},${c1y} ${c2x},${c2y} ${p[i + 1].x},${p[i + 1].y}`;
    });
});

/** Every curve segment as one path (the area's outline). */
const fullPath = computed(() => {
    const p = points.value;
    if (!p.length) return "";
    if (p.length === 1) return `M${p[0].x},${p[0].y}`;

    return `M${p[0].x},${p[0].y}${curveSegments.value.join("")}`;
});

/**
 * The newest bucket is still filling up (today, this hour, this month), so its
 * segment is drawn dashed rather than as a real drop.
 */
const solidPath = computed(() => {
    const p = points.value;
    if (p.length < 2) return fullPath.value;
    if (!props.partialLast) return fullPath.value;

    return `M${p[0].x},${p[0].y}${curveSegments.value.slice(0, -1).join("")}`;
});

const partialPath = computed(() => {
    const p = points.value;
    if (p.length < 2 || !props.partialLast) return "";

    return `M${p.at(-2).x},${p.at(-2).y}${curveSegments.value.at(-1)}`;
});

const areaPath = computed(() => {
    const p = points.value;
    if (p.length < 2) return "";
    const base = PAD.top + plotHeight.value;
    return `${fullPath.value} L${p.at(-1).x},${base} L${p[0].x},${base} Z`;
});

/** Up to ~7 evenly spread x labels, always including both ends. */
const xLabels = computed(() => {
    const n = props.series.length;
    if (!n) return [];

    const room = Math.max(2, Math.floor(plotWidth.value / 84));
    const step = Math.max(1, Math.ceil((n - 1) / (room - 1)));
    const indexes = [];

    for (let i = 0; i < n; i += step) indexes.push(i);
    if (indexes.at(-1) !== n - 1) {
        if (n - 1 - indexes.at(-1) < step / 2) indexes.pop();
        indexes.push(n - 1);
    }

    return indexes.map((index) => ({
        index,
        x: x(index),
        text: bucketLabel(props.series[index].date, props.grouping),
        anchor: index === 0 ? "start" : index === n - 1 ? "end" : "middle",
    }));
});

const format = (metric, value) => METRICS[metric].format(value);

const tooltip = computed(() => {
    if (active.value === null || !props.series[active.value]) return null;

    const point = props.series[active.value];
    const position = points.value[active.value];
    const flip = position.x > width.value * 0.62;

    return {
        point,
        x: position.x,
        y: position.y,
        style: {
            left: `${position.x}px`,
            top: `${Math.max(4, Math.min(position.y - 12, props.height - 150))}px`,
            transform: flip ? "translateX(calc(-100% - 14px))" : "translateX(14px)",
        },
        title: bucketLabel(point.date, props.grouping, { long: true }) + (props.partialLast && active.value === props.series.length - 1 ? " (so far)" : ""),
        rows: [props.metric, ...Object.keys(METRICS).filter((key) => key !== props.metric)].map((key) => ({
            key,
            label: METRICS[key].label,
            value: format(key, point[key]),
            lead: key === props.metric,
        })),
    };
});

function onPointer(event) {
    const rect = root.value.getBoundingClientRect();
    const relative = event.clientX - rect.left - PAD.left;
    const n = props.series.length;

    if (!n) return;

    active.value = Math.min(n - 1, Math.max(0, Math.round((relative / Math.max(1, plotWidth.value)) * (n - 1))));
}

function onKey(event) {
    const n = props.series.length;
    if (!n) return;

    if (event.key === "ArrowRight" || event.key === "ArrowLeft") {
        event.preventDefault();
        const step = event.key === "ArrowRight" ? 1 : -1;
        active.value = active.value === null ? (step > 0 ? 0 : n - 1) : Math.min(n - 1, Math.max(0, active.value + step));
    } else if (event.key === "Escape") {
        active.value = null;
    }
}

const summary = computed(() => {
    const total = values.value.reduce((sum, value) => sum + value, 0);
    const peak = Math.max(...values.value, 0);
    const label = METRICS[props.metric].label.toLowerCase();

    return `${METRICS[props.metric].label} by ${props.grouping}. Peak ${format(props.metric, peak)}${
        ["visitors", "pageviews"].includes(props.metric) ? `, ${format(props.metric, total)} ${label} in all` : ""
    }. Use the arrow keys to read each ${props.grouping}.`;
});
</script>

<template>
    <div
        ref="root"
        class="fa-trend"
        :style="{ height: `${height}px` }"
        tabindex="0"
        role="img"
        :aria-label="summary"
        @pointermove="onPointer"
        @pointerleave="active = null"
        @focus="active = active ?? series.length - 1"
        @blur="active = null"
        @keydown="onKey"
    >
        <svg :width="width" :height="height" :viewBox="`0 0 ${width} ${height}`" aria-hidden="true">
            <defs>
                <linearGradient :id="`fa-fill-${metric}`" x1="0" x2="0" y1="0" y2="1">
                    <stop offset="0%" class="fa-trend__fill-top" />
                    <stop offset="100%" class="fa-trend__fill-bottom" />
                </linearGradient>
            </defs>

            <g class="fa-trend__grid">
                <template v-for="tick in ticks" :key="tick">
                    <line :x1="PAD.left" :x2="width - PAD.right" :y1="y(tick)" :y2="y(tick)" :class="{ 'fa-trend__baseline': tick === 0 }" />
                    <text :x="PAD.left - 10" :y="y(tick)" dy="0.32em" text-anchor="end">{{ format(metric, tick) }}</text>
                </template>
            </g>

            <g class="fa-trend__axis">
                <text v-for="label in xLabels" :key="label.index" :x="label.x" :y="height - 8" :text-anchor="label.anchor">{{ label.text }}</text>
            </g>

            <path v-if="areaPath" :d="areaPath" :fill="`url(#fa-fill-${metric})`" class="fa-trend__area" />
            <path :d="solidPath" class="fa-trend__line" />
            <path v-if="partialPath" :d="partialPath" class="fa-trend__line fa-trend__line--partial" />
            <circle v-if="points.length === 1" :cx="points[0].x" :cy="points[0].y" r="4" class="fa-trend__dot" />

            <g v-if="tooltip">
                <line :x1="tooltip.x" :x2="tooltip.x" :y1="PAD.top" :y2="PAD.top + plotHeight" class="fa-trend__crosshair" />
                <circle :cx="tooltip.x" :cy="tooltip.y" r="5" class="fa-trend__dot" />
            </g>
        </svg>

        <div v-if="tooltip" class="fa-tooltip" :style="tooltip.style">
            <p class="fa-tooltip__title">{{ tooltip.title }}</p>
            <dl>
                <div v-for="row in tooltip.rows" :key="row.key" class="fa-tooltip__row" :class="{ 'is-lead': row.lead }">
                    <dt>
                        <span v-if="row.lead" class="fa-tooltip__key" />
                        {{ row.label }}
                    </dt>
                    <dd>{{ row.value }}</dd>
                </div>
            </dl>
        </div>

        <table class="fa-sr-only">
            <caption>{{ METRICS[metric].label }}</caption>
            <thead>
                <tr>
                    <th scope="col">Date</th>
                    <th v-for="(m, key) in METRICS" :key="key" scope="col">{{ m.label }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="point in series" :key="point.date">
                    <th scope="row">{{ bucketLabel(point.date, grouping, { long: true }) }}</th>
                    <td v-for="(m, key) in METRICS" :key="key">{{ format(key, point[key]) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped>
.fa-trend {
    position: relative;
    width: 100%;
    outline: none;
    touch-action: pan-y;
}

.fa-trend:focus-visible {
    box-shadow: 0 0 0 2px var(--fa-focus);
    border-radius: 8px;
}

.fa-tooltip {
    position: absolute;
    z-index: 5;
    min-width: 180px;
    pointer-events: none;
}

.fa-trend svg {
    display: block;
    overflow: visible;
}

.fa-trend__grid line {
    stroke: var(--fa-grid);
    stroke-width: 1;
    shape-rendering: crispEdges;
}

.fa-trend__grid line.fa-trend__baseline {
    stroke: var(--fa-axis);
}

.fa-trend__grid text,
.fa-trend__axis text {
    fill: var(--fa-muted);
    font-size: 11px;
    font-variant-numeric: tabular-nums;
}

.fa-trend__fill-top {
    stop-color: var(--fa-series);
    stop-opacity: 0.22;
}

.fa-trend__fill-bottom {
    stop-color: var(--fa-series);
    stop-opacity: 0.02;
}

.fa-trend__line {
    fill: none;
    stroke: var(--fa-series);
    stroke-width: 2;
    stroke-linejoin: round;
    stroke-linecap: round;
}

.fa-trend__line--partial {
    stroke-dasharray: 4 4;
}

.fa-trend__area,
.fa-trend__line {
    transition: d 400ms cubic-bezier(0.22, 1, 0.36, 1);
}

.fa-trend__crosshair {
    stroke: var(--fa-axis);
    stroke-width: 1;
    shape-rendering: crispEdges;
}

.fa-trend__dot {
    fill: var(--fa-series);
    stroke: var(--fa-surface);
    stroke-width: 2;
}
</style>
