<?php

return [

    'placeholder_image' => [
        'config' => [
            'placeholder_type' => [
                'display' => 'Placeholder type',
                'instructions' => 'The type of placeholder to generate. You can set the default type in `config/statamic/placeholders.php`.',
            ],
            'generate_on_upload' => [
                'display' => 'Generate on upload',
                'instructions' => 'Generate the placeholder image directly when uploading an asset. If disabled, placeholders will only be generated when requested.',
            ],
            'preview_placeholder' => [
                'display' => 'Show preview',
                'instructions' => 'Display a small preview of its generated placeholder image when editing an asset in the control panel.',
            ],
        ],
        'field' => [
            'generated' => 'Placeholder image generated',
            'not_generated' => 'No placeholder generated',
            'not_yet_generated' => 'Placeholder image will be generated on request',
            'no_asset' => 'not an asset',
            'not_supported' => 'unsupported filetype',
            'generate_on_save' => 'Generate placeholder on save',
            'show_preview' => 'Show',
            'hide_preview' => 'Hide',
        ],
    ],

];
