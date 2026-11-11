<script setup>
import { computed, ref } from "vue";
import { formatFull } from "./format";

/**
 * One whole split into parts (devices): segments in fixed categorical order
 * with a 2px surface gap, a legend that always names each part with its share,
 * and a tooltip per segment.
 */
const props = defineProps({
    parts: { type: Array, required: true }, // [{ name, visitors, color }]
    label: { type: String, required: true },
});

const hovered = ref(null);

const total = computed(() => props.parts.reduce((sum, part) => sum + part.visitors, 0));
const share = (part) => (total.value ? (part.visitors / total.value) * 100 : 0);
const pct = (part) => {
    const value = share(part);
    return value > 0 && value < 1 ? "<1%" : `${Math.round(value)}%`;
};
</script>

<template>
    <div class="fa-split">
        <div class="fa-split__bar" role="img" :aria-label="`${label}: ${parts.map((p) => `${p.name} ${pct(p)}`).join(', ')}`" @pointerleave="hovered = null">
            <span
                v-for="(part, index) in parts"
                :key="part.name"
                class="fa-split__segment"
                :class="{ 'is-dim': hovered !== null && hovered !== index }"
                :style="{ flexGrow: share(part), background: part.color }"
                @pointerenter="hovered = index"
            />
            <div v-if="hovered !== null && parts[hovered]" class="fa-tooltip fa-split__tip">
                <p class="fa-tooltip__title">{{ parts[hovered].name }}</p>
                <dl>
                    <div class="fa-tooltip__row is-lead">
                        <dt><span class="fa-tooltip__key" :style="{ background: parts[hovered].color }" />Visitors</dt>
                        <dd>{{ formatFull(parts[hovered].visitors) }}</dd>
                    </div>
                    <div class="fa-tooltip__row">
                        <dt>Share</dt>
                        <dd>{{ pct(parts[hovered]) }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <ul class="fa-split__legend">
            <li v-for="(part, index) in parts" :key="part.name" @pointerenter="hovered = index" @pointerleave="hovered = null">
                <span class="fa-split__swatch" :style="{ background: part.color }" />
                <span class="fa-split__name">{{ part.name }}</span>
                <span class="fa-split__pct">{{ pct(part) }}</span>
            </li>
        </ul>
    </div>
</template>

<style scoped>
.fa-split__bar {
    position: relative;
    display: flex;
    gap: 2px;
    height: 14px;
}

.fa-split__segment {
    min-width: 4px;
    border-radius: 4px;
    transition:
        flex-grow 600ms cubic-bezier(0.22, 1, 0.36, 1),
        opacity 150ms;
}

.fa-split__segment.is-dim {
    opacity: 0.35;
}

.fa-split__tip {
    position: absolute;
    top: 22px;
    left: 50%;
    z-index: 5;
    min-width: 170px;
    transform: translateX(-50%);
    pointer-events: none;
}

.fa-split__legend {
    display: flex;
    flex-wrap: wrap;
    gap: 6px 18px;
    margin-top: 12px;
}

.fa-split__legend li {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    font-size: 13px;
    color: var(--fa-ink-2);
}

.fa-split__swatch {
    width: 10px;
    height: 10px;
    border-radius: 3px;
}

.fa-split__name {
    color: var(--fa-ink);
}

.fa-split__pct {
    font-weight: 600;
    color: var(--fa-ink);
    font-variant-numeric: tabular-nums;
}
</style>
