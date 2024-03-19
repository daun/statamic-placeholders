<?php

return [

    'placeholder' => [
        'config' => [
            'placeholder_type' => [
                'display' => 'Placeholder type',
                'instructions' => 'The type of placeholder to generate. You can set the default type in `config/placeholders.php`.',
            ],
            'preview_placeholder' => [
                'display' => 'Show preview',
                'instructions' => 'Display a small preview of its generated placeholder image when editing an asset in the control panel.',
            ],
        ],
        'field' => [
            'generated' => 'Placeholder generated',
            'not_generated' => 'No placeholder',
            'not_yet_generated' => 'Not yet generated',
            'generated_on_request' => 'Will be generated on request',
            'no_asset' => 'not an asset',
            'not_supported' => 'unsupported filetype',
            'generate_on_save' => 'Generate on save',
            'show_preview' => 'Show',
            'hide_preview' => 'Hide',
        ],
    ],

];
