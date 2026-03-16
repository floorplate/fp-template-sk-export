<template>
    <div class="example-fieldtype-container">
        <Input :model-value="value" @update:model-value="update" />
    </div>
</template>

<script>
import { FieldtypeMixin as Fieldtype } from '@statamic/cms';
import { Input } from '@statamic/cms/ui';

export default {
    mixins: [Fieldtype],
    components: { Input },
    data() {
        return {}
    }
};
</script>
