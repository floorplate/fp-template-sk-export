<template>
    <div class="flex gap-2 sm:gap-3">
        <div class="w-fit shrink-0">
            <Select :options="modeOptions" v-model="mode" />
        </div>
        <div class="flex-1 min-w-0 flex flex-col gap-2">
            <Input
                v-if="mode === 'url'"
                :read-only="isReadOnly"
                v-model="urlValue"
            />

            <template v-if="mode === 'entry'">
                <relationship-fieldtype
                    ref="entries"
                    handle="entry"
                    button-size="base"
                    :config="meta.entry.config"
                    :meta="meta.entry.meta"
                    :value="selectedEntries"
                    @update:meta="meta.entry.meta = $event"
                    @update:value="entriesSelected"
                />
                <Select
                    v-if="selectedEntries.length && entryAnchorOptions.length"
                    :key="selectedEntries[0]"
                    :options="entryAnchorOptions"
                    :model-value="selectedSet"
                    clearable
                    :placeholder="__('Entire page — pick an anchor to deep-link')"
                    @update:model-value="setEntryAnchor"
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
            </template>

            <template v-if="mode === 'anchor'">
                <Select
                    v-if="pageAnchorOptions.length"
                    :options="pageAnchorOptions"
                    v-model="pageAnchorSet"
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
                <div v-else class="text-xs opacity-60 self-center">
                    No anchors declared on this page yet.
                </div>
            </template>
        </div>
    </div>
</template>

<script>
import { FieldtypeMixin as Fieldtype } from '@statamic/cms';
import { Input, Select } from '@statamic/cms/ui';
import { markRaw } from 'vue';

// Local rather than imported from @statamic/cms: `debounce` is not part of that
// package's public exports in every Statamic 6 release, and reading it off the
// runtime yields undefined, which throws in the created hook.
function debounce(fn, wait) {
    let timeout;

    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn.apply(this, args), wait);
    };
}

// Preview only — PHP's Str::slug is authoritative at save time.
function slugify(text) {
    return String(text)
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

export default {
    components: { Input, Select },

    mixins: [Fieldtype],

    provide: { isInLinkField: true },

    data() {
        return {
            mode: this.meta.initialMode,
            urlValue: this.meta.initialUrl,
            selectedEntries: this.meta.initialSelectedEntries,
            selectedSet: this.meta.initialSet,
            selectedAnchor: this.meta.initialAnchor,
            targetAnchors: this.meta.initialTargetAnchors || [],
            metaChanging: false,
        };
    },

    created() {
        this.syncUrlDebounced = markRaw(
            debounce((url) => {
                this.update(url || null);
                this.updateMeta({ ...this.meta, initialUrl: url });
            }, 150)
        );
    },

    computed: {
        // The publish container inject has no default; guard against
        // contexts that render fieldtypes outside one.
        container() {
            return this.injectedPublishContainer ? this.publishContainer : null;
        },

        anchorModeAvailable() {
            return (
                this.meta.showAnchorMode &&
                this.container?.reference?.startsWith('entry::') &&
                this.allowedModes.includes('anchor')
            );
        },

        allowedModes() {
            const configured = this.config.modes || [];
            return configured.length ? configured : ['url', 'entry', 'anchor'];
        },

        modeOptions() {
            return [
                this.config.required ? null : { label: __('None'), value: null },
                this.allowedModes.includes('url') ? { label: __('URL'), value: 'url' } : null,
                this.allowedModes.includes('entry') ? { label: __('Entry'), value: 'entry' } : null,
                this.anchorModeAvailable ? { label: __('This page'), value: 'anchor' } : null,
            ].filter(Boolean);
        },

        // Anchors currently declared in the live publish form — reactive to
        // unsaved edits, so a just-typed anchor is immediately pickable.
        livePageAnchors() {
            const container = this.container;
            if (!container?.blueprint || !container?.values) return [];

            const records = [];

            const collectFromFields = (fields, values) => {
                (fields || []).forEach((field) => {
                    if (field.type === 'anchor') {
                        const raw = values?.[field.handle];
                        if (raw) {
                            const slug = slugify(raw);
                            records.push({
                                set: null,
                                anchor: slug,
                                display: field.display || slug,
                                label: slug,
                            });
                        }
                    }

                    if ((field.type === 'replicator' || field.type === 'bard') && Array.isArray(field.sets)) {
                        const setDefs = {};
                        field.sets.forEach((group) =>
                            (group.sets || []).forEach((set) => (setDefs[set.handle] = set))
                        );

                        const rows = values?.[field.handle];
                        if (!Array.isArray(rows)) return;

                        rows.forEach((row) => {
                            if (!row || typeof row !== 'object') return;

                            // Bard set rows nest values under attrs.
                            const isBardSet = row.type === 'set' && row.attrs?.values;
                            const rowValues = isBardSet ? row.attrs.values : row;
                            const def = setDefs[rowValues?.type];
                            if (!def) return;

                            const enabled = (isBardSet ? row.attrs?.enabled : row.enabled) !== false;
                            const id = isBardSet ? row.attrs?.id : row._id ?? row.id;
                            if (!enabled || !id) return;

                            (def.fields || []).forEach((f) => {
                                if (f.type !== 'anchor') return;
                                const raw = rowValues?.[f.handle];
                                if (!raw) return;
                                const slug = slugify(raw);
                                const display = def.display || rowValues.type;
                                records.push({
                                    set: id,
                                    anchor: slug,
                                    display,
                                    label: `${display} #${slug}`,
                                });
                            });
                        });
                    }
                });
            };

            (container.blueprint.tabs || []).forEach((tab) =>
                (tab.sections || []).forEach((section) =>
                    collectFromFields(section.fields, container.values)
                )
            );

            return records;
        },

        pageAnchorOptions() {
            const options = this.livePageAnchors.map((record) => ({
                label: record.label,
                value: record.set,
                display: record.display,
                anchor: record.anchor,
            }));

            // A saved reference whose block vanished from the live form still
            // needs a visible selection.
            if (this.selectedSet && !options.some((option) => option.value === this.selectedSet)) {
                options.push({
                    label: `#${this.selectedAnchor || '?'} (missing)`,
                    value: this.selectedSet,
                    display: __('Missing block'),
                    anchor: this.selectedAnchor,
                });
            }

            return options;
        },

        // No "(entire page)" pseudo-option — an empty-string option value breaks
        // reka-ui's combobox collection (it registers zero options). Clearing the
        // Select (clearable) represents "link to the whole entry" instead.
        entryAnchorOptions() {
            const options = this.targetAnchors.map((record) => ({
                label: record.label,
                value: record.set,
                display: record.display,
                anchor: record.anchor,
            }));

            if (this.selectedSet && !options.some((option) => option.value === this.selectedSet)) {
                options.push({
                    label: `#${this.selectedAnchor || '?'} (missing)`,
                    value: this.selectedSet,
                    display: __('Missing block'),
                    anchor: this.selectedAnchor,
                });
            }

            return options;
        },

        pageAnchorSet: {
            get() {
                return this.selectedSet;
            },
            set(setId) {
                const record = this.livePageAnchors.find((r) => r.set === setId);
                this.selectedSet = setId;
                this.selectedAnchor = record ? record.anchor : this.selectedAnchor;
                this.commitPageAnchor();
            },
        },

        replicatorPreview() {
            if (!this.showFieldPreviews) return;

            switch (this.mode) {
                case 'url':
                    return this.urlValue;
                case 'entry': {
                    const title = this.meta.entry?.meta?.data?.[0]?.title || '';
                    return this.selectedAnchor ? `${title} #${this.selectedAnchor}` : title;
                }
                case 'anchor':
                    return this.selectedAnchor ? `#${this.selectedAnchor}` : '';
            }

            return this.value;
        },
    },

    watch: {
        mode(mode) {
            if (this.metaChanging) return;

            if (mode === null) {
                this.update(null);
            } else if (mode === 'url') {
                this.syncUrlDebounced(this.urlValue);
            } else if (mode === 'entry') {
                if (this.selectedEntries.length) {
                    this.commitEntry();
                } else {
                    setTimeout(() => this.$refs.entries?.linkExistingItem?.(), 0);
                }
            } else if (mode === 'anchor') {
                if (this.selectedSet) this.commitPageAnchor();
            }

            this.updateMeta({ ...this.meta, initialMode: mode });
        },

        urlValue(url) {
            if (this.metaChanging || this.mode !== 'url') return;
            this.syncUrlDebounced(url);
        },

        meta(meta, oldMeta) {
            if (meta === oldMeta) return;
            if (JSON.stringify(meta) === JSON.stringify(oldMeta)) return;
            this.metaChanging = true;
            this.mode = meta.initialMode;
            this.urlValue = meta.initialUrl;
            this.selectedEntries = meta.initialSelectedEntries;
            this.selectedSet = meta.initialSet;
            this.selectedAnchor = meta.initialAnchor;
            this.targetAnchors = meta.initialTargetAnchors || [];
            this.$nextTick(() => (this.metaChanging = false));
        },
    },

    methods: {
        entriesSelected(entries) {
            const previous = this.selectedEntries[0] || null;
            this.selectedEntries = entries;

            const entry = entries[0] || null;

            if (entry !== previous) {
                this.selectedSet = null;
                this.selectedAnchor = null;
                this.targetAnchors = [];
                if (entry) this.fetchAnchors(entry);
            }

            this.commitEntry();
        },

        fetchAnchors(entryId) {
            this.$axios
                .get(this.meta.anchorsIndexUrl.replace('_ID_', entryId))
                .then((response) => {
                    this.targetAnchors = response.data.data || [];
                    this.updateMeta({ ...this.meta, initialTargetAnchors: this.targetAnchors });
                })
                .catch(() => (this.targetAnchors = []));
        },

        // Clearing the Select (setId null/undefined) links to the entire entry.
        setEntryAnchor(setId) {
            const record = this.targetAnchors.find((r) => r.set === setId);
            this.selectedSet = setId || null;
            this.selectedAnchor = record ? record.anchor : null;
            this.commitEntry();
        },

        commitEntry() {
            const entry = this.selectedEntries[0] || null;

            if (!entry) {
                this.update(null);
            } else {
                const value = { mode: 'entry', entry };
                if (this.selectedSet) {
                    value.set = this.selectedSet;
                    value.anchor = this.selectedAnchor;
                }
                this.update(value);
            }

            this.updateMeta({
                ...this.meta,
                initialSelectedEntries: this.selectedEntries,
                initialSet: this.selectedSet,
                initialAnchor: this.selectedAnchor,
            });
        },

        commitPageAnchor() {
            this.update(
                this.selectedSet
                    ? { mode: 'anchor', set: this.selectedSet, anchor: this.selectedAnchor }
                    : null
            );

            this.updateMeta({
                ...this.meta,
                initialSet: this.selectedSet,
                initialAnchor: this.selectedAnchor,
            });
        },
    },
};
</script>
