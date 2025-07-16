<template>

    <div>
        <select v-model="value" @change="update($event.target.value)" class="w-full border rounded px-2 py-1 text-black">
            <option v-for="font in fonts" :key="font" :value="font">
                {{ font }}
            </option>
        </select>
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
    }

};
</script>
