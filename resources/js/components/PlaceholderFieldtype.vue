<template>
    <div class="flex flex-column gap-2">
        <ui-badge v-if="!isAsset" pill icon="focus" color="white" v-tooltip="t('unmirrored_no_asset')">
            {{ t('ungenerated') }}
        </ui-badge>
        <ui-badge v-else-if="!isSupported" pill icon="focus" color="white" v-tooltip="t('ungenerated_no_image')">
            {{ t('ungenerated') }}
        </ui-badge>
        <ui-badge v-else-if="!isGenerated && generateOnUpload" pill icon="time-clock" color="white">
            {{ t('not_yet_generated') }}
        </ui-badge>
        <ui-badge v-else-if="!isGenerated" pill icon="time-clock" color="white">
            {{ t('generated_on_request') }}
        </ui-badge>
        <ui-badge v-else-if="isGenerated && !showPreview" pill icon="checkmark" color="green">
            {{ t('generated') }}
        </ui-badge>
        <template v-else-if="isGenerated && showPreview">
            <div v-if="!showingPreview" class="flex gap-2">
                <ui-button size="sm" class="p-0! overflow-hidden w-auto" @click="showingPreview = !showingPreview">
                    <img :src="uri" class="h-8" />
                </ui-button>
                <ui-button size="sm" @click="showingPreview = !showingPreview">
                    {{ t(showingPreview ? 'hide_preview_btn' : 'show_preview_btn') }}
                </ui-button>
            </div>
            <div v-else class="flex flex-wrap gap-4">
                <div class="grow-0 shrink-0 relative group/thumb inline-block">
                    <img :src="uri" class="btn p-0 h-8 min-h-40 w-auto rounded-lg cursor-pointer" @click="showingPreview = false" />
                    <div class="absolute top-0 rtl:left-0 ltr:right-0 opacity-0 group-hover/thumb:opacity-50">
                        <ui-button
                            text="×"
                            variant="ghost"
                            style="--tw-bg-opacity: 0;"
                            :aria-label="t('hide_preview')"
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
        </template>
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
        isGenerated() {
            return !! this.hash;
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
    },
    methods: {
        t(key, replacements = {}) {
            return __(`statamic-placeholders::fieldtypes.placeholder.field.${key}`, replacements);
        }
    },
};
</script>
