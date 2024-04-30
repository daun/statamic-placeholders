<template>
    <div>
        <div v-if="!isAsset || !isSupported" class="help-block mb-0 flex items-center">
            <svg-icon name="hidden" class="h-4" />
            <span class="ml-2">
                {{ __('statamic-placeholders::fieldtypes.placeholder.field.not_generated') }}:
                <template v-if="!isSupported">
                    {{ __('statamic-placeholders::fieldtypes.placeholder.field.not_supported') }}
                </template>
                <template v-else>
                    {{ __('statamic-placeholders::fieldtypes.placeholder.field.no_asset') }}
                </template>
            </span>
        </div>
        <div v-else-if="!uri">
            <div class="help-block mb-0 flex items-center">
                <svg-icon name="close" class="h-4" />
                <span class="ml-2">
                    <template v-if="generateOnUpload">
                        {{ __('statamic-placeholders::fieldtypes.placeholder.field.not_yet_generated') }}
                    </template>
                    <template v-else>
                        {{ __('statamic-placeholders::fieldtypes.placeholder.field.generated_on_request') }}
                    </template>
                </span>
            </div>
            <!-- <div v-if="allowGenerate" class="flex items-center mt-3">
                <label for="upload-asset" class="flex items-center cursor-pointer">
                    <input type="checkbox" name="remember" id="generate-placeholder" v-model="value.generate">
                    <span class="ml-2">{{ __('statamic-placeholders::fieldtypes.placeholder.field.generate_on_save') }}</span>
                </label>
            </div> -->
        </div>
        <div v-else>
            <div v-if="!showPreview" class="help-block mb-0 flex items-center">
                <svg-icon name="synchronize" class="h-4" />
                <span :title="this.value.id" class="ml-2">
                    {{ __('statamic-placeholders::fieldtypes.placeholder.field.generated') }}
                </span>
            </div>
            <div v-if="showPreview" class="mt-3">
                <div v-if="!showingPreview" class="flex gap-2">
                    <img @click="showingPreview = !showingPreview" :src="uri" class="btn btn-flat btn-sm p-0 overflow-hidden w-auto" :class="{ 'opacity-25': showingPreview }" />
                    <button @click="showingPreview = !showingPreview" type="button" class="btn btn-flat btn-sm">
                        <template v-if="showingPreview">
                            {{ __('statamic-placeholders::fieldtypes.placeholder.field.hide_preview') }}
                        </template>
                        <template v-else>
                            {{ __('statamic-placeholders::fieldtypes.placeholder.field.show_preview') }}
                        </template>
                    </button>
                </div>
                <div v-else class="flex flex-wrap gap-4">
                    <div @click="showingPreview = false" class="grow-0 shrink-0 relative group inline-block">
                        <img :src="uri" class="btn p-0 h-8 min-h-40 w-auto rounded-md" />
                        <button
                            aria-label="Hide Preview"
                            class="btn-close absolute top-2 rtl:left-2.5 ltr:right-2.5 opacity-0 group-hover:opacity-100"
                            style="--tw-bg-opacity: 0;"
                        >×</button>
                    </div>
                    <div class="grow shrink basis-40 overflow-hidden text-xs text-gray-700 font-mono">
                        <div v-if="provider">{{ provider.name }}</div>
                        <div v-if="hash" title="hash" class="truncate">{{ hash }}</div>
                        <div v-if="size">{{ size }}</div>
                    </div>
                </div>
            </div>
            <!-- <div v-if="allowRegenerate" class="flex items-center mt-3">
                <label for="upload-asset" class="help-block mb-0 flex items-center cursor-pointer font-normal">
                    <input type="checkbox" name="remember" id="upload-asset" v-model="value.upload">
                    <span class="ml-2">{{ __('statamic-placeholders::fieldtypes.placeholder.field.reupload_on_save') }}</span>
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
            return this.config?.allow_generate;
        },
        allowRegenerate() {
            return this.config?.allow_regenerate ?? this.config?.allow_generate;
        },
        generateOnUpload() {
            return this.meta?.generate_on_upload;
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
        },
        size() {
            return this.meta?.size;
        }
    }
};
</script>
