<template>

    <div class="flex gap-2 items-center">
        <select v-model="value" @change="update($event.target.value)" class="flex-1 border rounded px-2 py-1 text-black">
            <option value="">-- Select a font --</option>
            <option v-for="font in fonts" :key="font" :value="font">
                {{ font }}
            </option>
        </select>
        <button
            v-if="value"
            @click="clearValue"
            type="button"
            class="px-2 text-gray-500 hover:text-gray-700 focus:outline-none"
            title="Clear selection"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

</template>

<script>

import fontData from '../../../data/transformed_fonts.json';

export default {

    mixins: [Fieldtype],

    data() {
        return {
            fonts: []
        };
    },
    mounted() {
        const allFonts = (fontData.fonts || []).map(f => f.family);
        this.fonts = [...new Set(allFonts)];
    },
    methods: {
        clearValue() {
            this.update('');
        }
    }

};
</script>
