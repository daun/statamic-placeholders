<template>
    <div class="flex items-center" v-if="isAsset && isSupported">
        <ui-icon v-if="isGenerated" name="checkmark" class="text-green-600" />
        <ui-icon v-else name="x-square" class="text-gray-400 dark:text-gray-600" />
    </div>
</template>

<script>
import { IndexFieldtypeMixin as IndexFieldtype } from '@statamic/cms';

export default {
    mixins: [IndexFieldtype],
    computed: {
        extension() {
            return this.values.extension.toLowerCase();
        },
        isAsset() {
            return this.values.basename && this.values.extension;
        },
        isSupported() {
            return ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'svg'].includes(this.extension);
        },
        isGenerated() {
            return Object.keys(this.value || {}).length > 0;
        },
    },
};
</script>
