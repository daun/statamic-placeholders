import statamicPreset from './vendor/statamic/cms/tailwind.config.js'

console.log(statamicPreset)

export default {
    prefix: 'seo-',
    presets: [
        statamicPreset
    ],
    content: [
        './resources/**/*.{html,js,vue,blade.php}',
        './tests/**/*.{html,vue,blade.php}'
    ],
    corePlugins: {
        // preflight: false,
    },
}
