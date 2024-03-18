<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Generate Placeholder Images for Assets
    |--------------------------------------------------------------------------
    |
    | Add a `placeholder_image` field to an asset blueprint to generate a
    | placeholder for any image uploaded to that container. Use the flag below
    | to disable this feature without removing the field from the blueprint.
    |
    */

    'enabled' => env('PLACEHOLDER_IMAGES_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Default Provider
    |--------------------------------------------------------------------------
    |
    | The default placeholder type to use. Each `placeholder_image` field can
    | choose to either use this default provider or pick a different provider
    | than the default. If you manually generate placeholders for URLs, this
    |  default provider will also be used unless specified otherwise.
    |
    | Available options: Thumbhash, Blurhash, AverageColor
    |
    */

    'default_provider' => Daun\StatamicPlaceholders\Providers\Thumbhash::class,

    /*
    |--------------------------------------------------------------------------
    | Placeholder Providers
    |--------------------------------------------------------------------------
    |
    | The addon ships with a set of default placeholder providers to pick from:
    | 'thumbhash', 'blurhash' and 'average'. You can also provide your own
    | types of placeholders by implementing the `PlaceholderProvider` interface
    | and adding the provider class to the list below.
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
