<script setup>
import { computed, ref } from "vue";
import { formatCount, formatFull } from "./format";

/**
 * A ranked list with a soft bar behind each row sized to its share of the
 * top row. Values are text in ink colours; the bar only carries magnitude.
 * Hovering or focusing a row shows a detail card (the `tip` slot).
 */
const props = defineProps({
    rows: { type: Array, required: true }, // [{ key, label, value, href?, ... }]
    columnLabel: { type: String, required: true },
    valueLabel: { type: String, default: "Visitors" },
    total: { type: Number, default: null }, // for shares; defaults to the sum of rows
    empty: { type: String, default: "Nothing yet for this period." },
});

const hovered = ref(null);

const max = computed(() => Math.max(1, ...props.rows.map((row) => row.value)));
const sum = computed(() => props.total ?? props.rows.reduce((total, row) => total + row.value, 0));

const share = (row) => (sum.value ? Math.round((row.value / sum.value) * 100) : 0);

const tipTop = ref(0);

function show(index, event) {
    hovered.value = index;
    const item = event.currentTarget;
    tipTop.value = item.offsetTop + item.offsetHeight + 6;
}
</script>

<template>
    <div class="fa-bars" @pointerleave="hovered = null">
        <div class="fa-bars__head">
            <span>{{ columnLabel }}</span>
            <span>{{ valueLabel }}</span>
        </div>

        <p v-if="!rows.length" class="fa-empty">{{ empty }}</p>

        <ul v-else>
            <li v-for="(row, index) in rows" :key="row.key ?? row.label">
                <component
                    :is="row.href ? 'a' : 'div'"
                    :href="row.href"
                    :target="row.href ? '_blank' : null"
                    :rel="row.href ? 'noopener' : null"
                    class="fa-bar"
                    :class="{ 'is-active': hovered === index }"
                    :tabindex="row.href ? null : 0"
                    @pointerenter="show(index, $event)"
                    @focus="show(index, $event)"
                    @blur="hovered = null"
                >
                    <span class="fa-bar__fill" :style="{ width: `${Math.max(1.5, (row.value / max) * 100)}%` }" />
                    <span class="fa-bar__label">
                        <slot name="label" :row="row">{{ row.label }}</slot>
                    </span>
                    <span class="fa-bar__share">{{ share(row) }}%</span>
                    <span class="fa-bar__value" :title="formatFull(row.value)">{{ formatCount(row.value) }}</span>
                </component>
            </li>
        </ul>

        <div v-if="hovered !== null && rows[hovered] && $slots.tip" class="fa-tooltip fa-bars__tip" :style="{ top: `${tipTop}px` }">
            <slot name="tip" :row="rows[hovered]" :share="share(rows[hovered])" />
        </div>
    </div>
</template>

<style scoped>
.fa-bars {
    position: relative;
}

.fa-bars__head {
    display: flex;
    justify-content: space-between;
    padding: 0 10px 6px;
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.02em;
    color: var(--fa-muted);
    text-transform: uppercase;
}

.fa-bars ul {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.fa-bar {
    position: relative;
    display: flex;
    align-items: center;
    gap: 10px;
    min-height: 32px;
    padding: 0 10px;
    border-radius: 6px;
    color: var(--fa-ink);
    text-decoration: none;
    outline: none;
    isolation: isolate;
    cursor: default;
}

a.fa-bar {
    cursor: pointer;
}

.fa-bar:focus-visible {
    box-shadow: 0 0 0 2px var(--fa-focus);
}

.fa-bar__fill {
    position: absolute;
    inset: 0 auto 0 0;
    z-index: -1;
    border-radius: 6px;
    background: var(--fa-wash);
    transition:
        width 600ms cubic-bezier(0.22, 1, 0.36, 1),
        background-color 150ms;
}

.fa-bar.is-active .fa-bar__fill {
    background: var(--fa-wash-strong);
}

.fa-bar__label {
    display: flex;
    flex: 1;
    align-items: center;
    gap: 8px;
    min-width: 0;
    overflow: hidden;
    font-size: 13px;
    white-space: nowrap;
    text-overflow: ellipsis;
}

.fa-bar__share {
    font-size: 12px;
    color: var(--fa-muted);
    font-variant-numeric: tabular-nums;
    opacity: 0;
    transition: opacity 150ms;
}

.fa-bar.is-active .fa-bar__share {
    opacity: 1;
}

.fa-bar__value {
    min-width: 3ch;
    font-size: 13px;
    font-weight: 600;
    text-align: right;
    font-variant-numeric: tabular-nums;
}

.fa-bars__tip {
    position: absolute;
    right: 0;
    z-index: 5;
    min-width: 200px;
    max-width: min(320px, 100%);
    pointer-events: none;
}
</style>
