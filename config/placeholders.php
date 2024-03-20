<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Generate Placeholder Images for Assets
    |--------------------------------------------------------------------------
    |
    | You need to a `placeholder` field to an asset blueprint to generate
    | placeholders for any image uploaded to that container. You can use the
    | flag below to disable generating placeholders without having to remove
    | the field from your blueprints, e.g. temporarily for development.
    |
    */

    'enabled' => env('PLACEHOLDERS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Generate Placeholders on Upload
    |--------------------------------------------------------------------------
    |
    | By default, placeholders will be generated on upload, ensuring they are
    | available on first use. You can opt out of this behavior here and have the
    | placeholders generated on demand.
    |
    */

    'generate_on_upload' => true,

    /*
    |--------------------------------------------------------------------------
    | Default Provider
    |--------------------------------------------------------------------------
    |
    | The default placeholder type to use. Each `placeholder` field can
    | choose to either use this default provider or pick a different provider
    | than the default. If you manually generate placeholders for URLs, this
    |  default provider will also be used unless specified otherwise.
    |
    | Available options: 'thumbhash', 'blurhash', 'average'
    |
    */

    'default_provider' => 'thumbhash',

    /*
    |--------------------------------------------------------------------------
    | Placeholder Providers
    |--------------------------------------------------------------------------
    |
    | The addon ships with a set of default placeholder providers to pick from:
    | 'thumbhash', 'blurhash' and 'average'. You can also provide your own
    | types of placeholders by extending the `AbstractPlaceholderProvider` class
    | and adding the resulting provider class to the list below.
    |
    */

    'providers' => [
        // \App\Providers\CustomColorPlaceholder::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Placeholder
    |--------------------------------------------------------------------------
    |
    | The fallback placeholder to use when an image placeholder is not yet
    | generated or has failed to generate. This can be a base64 encoded image
    | or a remote URL. Defaults to a transparent gif.
    |
    */

    'fallback_uri' => 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==',

    /*
    |--------------------------------------------------------------------------
    | Queue Driver
    |--------------------------------------------------------------------------
    |
    | Define the queue to use for processing placeholder generation jobs.
    | Leave empty to use the default connection and queue of your app.
    |
    */

    'queue' => [

        'connection' => null,

        'queue' => null,

    ],
];
