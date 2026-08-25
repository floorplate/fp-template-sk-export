<template>
    <div>
        <Select
            v-if="options.length"
            :options="options"
            :model-value="selectedSet"
            clearable
            :placeholder="__('Entire page — pick a section to deep-link')"
            @update:model-value="select"
        >
            <template #option="option">
                <span class="flex flex-col items-start text-left leading-tight w-full min-w-0">
                    <span class="truncate max-w-full">{{ option.display }}</span>
                    <span v-if="option.anchor" class="text-xs opacity-60 truncate max-w-full">#{{ option.anchor }}</span>
                </span>
            </template>
            <template #selected-option="{ option }">
                <span class="block truncate text-left">{{ option.display }}<span v-if="option.anchor" class="text-xs opacity-60"> #{{ option.anchor }}</span></span>
            </template>
        </Select>
        <div v-else class="text-xs opacity-60">
            {{ __('No sections on the linked page have an Anchor ID yet.') }}
        </div>
    </div>
</template>

<script>
import { FieldtypeMixin as Fieldtype } from '@statamic/cms';
import { Select } from '@statamic/cms/ui';

// Preview only — PHP's Str::slug is authoritative at save time.
function slugify(text) {
    return String(text)
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

export default {
    components: { Select },

    mixins: [Fieldtype],

    data() {
        return {
            selectedSet: this.value?.set ?? null,
            selectedAnchor: this.value?.anchor ?? null,
        };
    },

    computed: {
        // The publish container inject has no default; guard against contexts
        // that render fieldtypes outside one.
        container() {
            return this.injectedPublishContainer ? this.publishContainer : null;
        },

        /**
         * Anchors on the entry this item links to.
         *
         * A nav item's publish container carries the linked entry's values
         * merged in, but not its blueprint — so which fields declare anchors
         * comes from the server-supplied field map rather than from walking a
         * blueprint like the "this page" picker does.
         */
        anchors() {
            const values = this.container?.values;
            const replicators = this.meta.fieldMap?.replicators;

            if (!values || !replicators) return [];

            const records = [];

            Object.entries(replicators).forEach(([fieldHandle, sets]) => {
                const rows = values[fieldHandle];
                if (!Array.isArray(rows)) return;

                rows.forEach((row) => {
                    if (!row || typeof row !== 'object') return;

                    // Bard set rows nest values under attrs; Replicator rows are flat.
                    const isBardSet = row.type === 'set' && row.attrs?.values;
                    const rowValues = isBardSet ? row.attrs.values : row;
                    const setDef = sets[rowValues?.type];
                    if (!setDef) return;

                    const enabled = (isBardSet ? row.attrs?.enabled : row.enabled) !== false;
                    const id = isBardSet ? row.attrs?.id : (row._id ?? row.id);
                    if (!enabled || !id) return;

                    setDef.fields.forEach((handle) => {
                        const raw = rowValues[handle];
                        if (!raw) return;

                        records.push({ set: id, anchor: slugify(raw), display: setDef.display });
                    });
                });
            });

            return records;
        },

        options() {
            const options = this.anchors.map((record) => ({
                label: `${record.display} #${record.anchor}`,
                value: record.set,
                display: record.display,
                anchor: record.anchor,
            }));

            // A saved reference whose section is gone still needs a visible selection.
            if (this.selectedSet && !options.some((option) => option.value === this.selectedSet)) {
                options.push({
                    label: `#${this.selectedAnchor || '?'} (missing)`,
                    value: this.selectedSet,
                    display: __('Missing section'),
                    anchor: this.selectedAnchor,
                });
            }

            return options;
        },

        replicatorPreview() {
            if (!this.showFieldPreviews) return;

            return this.selectedAnchor ? `#${this.selectedAnchor}` : '';
        },
    },

    methods: {
        // Clearing the Select links to the whole page.
        select(setId) {
            const record = this.anchors.find((r) => r.set === setId);

            this.selectedSet = setId || null;
            this.selectedAnchor = record ? record.anchor : null;

            this.update(this.selectedSet ? { set: this.selectedSet, anchor: this.selectedAnchor } : null);
        },
    },
};
</script>
