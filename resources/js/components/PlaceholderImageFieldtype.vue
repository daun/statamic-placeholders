<template>
    <div>
        <div v-if="!isAsset || !isSupported" class="help-block flex items-center mb-3">
            <svg-icon name="hidden" class="h-4" />
            <span class="ml-2">
                {{ __('statamic-placeholder-images::fieldtypes.placeholder_image.field.not_generated') }}:
                <template v-if="!isSupported">
                    {{ __('statamic-placeholder-images::fieldtypes.placeholder_image.field.not_supported') }}
                </template>
                <template v-else>
                    {{ __('statamic-placeholder-images::fieldtypes.placeholder_image.field.no_asset') }}
                </template>
            </span>
        </div>
        <div v-else-if="!uri">
            <div class="help-block flex items-center mb-3">
                <svg-icon name="close" class="h-4" />
                <span class="ml-2">
                    {{ __('statamic-placeholder-images::fieldtypes.placeholder_image.field.not_yet_generated') }}
                </span>
            </div>
            <!-- <div v-if="allowGenerate" class="flex items-center">
                <label for="upload-asset" class="flex items-center cursor-pointer">
                    <input type="checkbox" name="remember" id="generate-placeholder" v-model="value.generate">
                    <span class="ml-2">{{ __('statamic-placeholder-images::fieldtypes.placeholder_image.field.generate_on_save') }}</span>
                </label>
            </div> -->
        </div>
        <div v-else>
            <div v-if="!showPreview" class="help-block flex items-center">
                <svg-icon name="synchronize" class="h-4" />
                <span :title="this.value.id" class="ml-2">
                    {{ __('statamic-placeholder-images::fieldtypes.placeholder_image.field.generated') }}
                </span>
            </div>
            <div v-if="showPreview" class="mt-3">
                <div class="flex">
                    <img :src="uri" class="h-8 w-auto rounded" :class="{ 'opacity-25': showingPreview }" />
                    <button @click="showingPreview = !showingPreview" type="button" class="btn btn-flat btn-sm ml-2">
                        <template v-if="showingPreview">
                            {{ __('statamic-placeholder-images::fieldtypes.placeholder_image.field.hide_preview') }}
                        </template>
                        <template v-else>
                            {{ __('statamic-placeholder-images::fieldtypes.placeholder_image.field.show_preview') }}
                        </template>
                    </button>
                </div>
                <img v-if="showingPreview" :src="uri" class="w-32 rounded-md mt-3" />
            </div>
            <!-- <div v-if="allowRegenerate" class="flex items-center mt-3">
                <label for="upload-asset" class="help-block flex items-center cursor-pointer font-normal">
                    <input type="checkbox" name="remember" id="upload-asset" v-model="value.upload">
                    <span class="ml-2">{{ __('statamic-placeholder-images::fieldtypes.placeholder_image.field.reupload_on_save') }}</span>
                </label>
            </div> -->
        </div>
    </div>
</template>

<script>
export default {
    mixins: [Fieldtype],
    data() {
        return {
            showingPreview: false
        }
    },
    computed: {
        allowGenerate() {
            return this.config?.generate_on_upload;
        },
        showPreview() {
            return this.config?.preview_placeholder;
        },
        isAsset() {
            return this.meta?.is_asset || false;
        },
        isSupported() {
            return this.meta?.is_supported || false;
        },
        uri() {
            return this.meta?.uri;
        },
        hash() {
            return this.meta?.hash;
        },
        provider() {
            return this.meta?.provider;
        }
    }
};
</script>
