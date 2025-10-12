<template>
    <div>
        <div v-if="!isAsset || !isSupported">
            <DescriptionWithIcon icon="eye-slash">
                {{ __('statamic-placeholders::fieldtypes.placeholder.field.not_generated') }}:
                <template v-if="!isSupported">
                    {{ __('statamic-placeholders::fieldtypes.placeholder.field.not_supported') }}
                </template>
                <template v-else>
                    {{ __('statamic-placeholders::fieldtypes.placeholder.field.no_asset') }}
                </template>
            </DescriptionWithIcon>
        </div>
        <div v-else-if="!uri">
            <DescriptionWithIcon icon="time-clock">
                <template v-if="generateOnUpload">
                    {{ __('statamic-placeholders::fieldtypes.placeholder.field.not_yet_generated') }}
                </template>
                <template v-else>
                    {{ __('statamic-placeholders::fieldtypes.placeholder.field.generated_on_request') }}
                </template>
            </DescriptionWithIcon>
            <!-- <div v-if="allowGenerate" class="flex items-center mt-3">
                <label for="upload-asset" class="flex items-center cursor-pointer">
                    <input type="checkbox" name="remember" id="generate-placeholder" v-model="value.generate">
                    <span class="ml-2">{{ __('statamic-placeholders::fieldtypes.placeholder.field.generate_on_save') }}</span>
                </label>
            </div> -->
        </div>
        <div v-else>
            <DescriptionWithIcon v-if="!showPreview" icon="focus">
                <span :title="this.value.id">
                    {{ __('statamic-placeholders::fieldtypes.placeholder.field.generated') }}
                </span>
            </DescriptionWithIcon>
            <div v-if="showPreview" class="mt-3">
                <div v-if="!showingPreview" class="flex gap-2">
                    <Button size="sm" class="p-0 overflow-hidden w-auto" @click="showingPreview = !showingPreview">
                        <img :src="uri" class="h-8" />
                    </Button>
                    <Button size="sm" @click="showingPreview = !showingPreview">
                        <template v-if="showingPreview">
                            {{ __('statamic-placeholders::fieldtypes.placeholder.field.hide_preview_btn') }}
                        </template>
                        <template v-else>
                            {{ __('statamic-placeholders::fieldtypes.placeholder.field.show_preview_btn') }}
                        </template>
                    </Button>
                </div>
                <div v-else class="flex flex-wrap gap-4">
                    <div class="grow-0 shrink-0 relative group/thumb inline-block">
                        <img :src="uri" class="btn p-0 h-8 min-h-40 w-auto rounded-md cursor-pointer" @click="showingPreview = false" />
                        <div class="absolute top-0 rtl:left-0 ltr:right-0 opacity-0 group-hover/thumb:opacity-50">
                            <Button
                                text="×"
                                variant="ghost"
                                style="--tw-bg-opacity: 0;"
                                :aria-label="__('statamic-placeholders::fieldtypes.placeholder.field.hide_preview')"
                                @click="showingPreview = false"
                            />
                        </div>
                    </div>
                    <div class="grow shrink basis-40 overflow-hidden text-xs text-gray-500 font-mono">
                        <div v-if="provider">{{ provider.name }}</div>
                        <div v-if="hash" class="truncate">{{ hash }}</div>
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
import { FieldtypeMixin as Fieldtype } from '@statamic/cms';
import { Button, Description, Icon } from '@statamic/cms/ui';
import DescriptionWithIcon from './DescriptionWithIcon.vue';

export default {
    mixins: [Fieldtype],
    components: {
        Button,
        Description,
        DescriptionWithIcon,
        Icon
    },
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
