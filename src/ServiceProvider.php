<?php

namespace Daun\StatamicPlaceholders;

use Daun\StatamicPlaceholders\Services\PlaceholderProviders;
use Daun\StatamicPlaceholders\Services\PlaceholderService;
use Statamic\Events\AssetReuploaded;
use Statamic\Events\AssetSaved;
use Statamic\Events\AssetUploaded;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $commands = [
        Commands\Generate::class,
    ];

    protected $listen = [
        // AssetSaved::class => [Listeners\GeneratePlaceholder::class],
        AssetUploaded::class => [Listeners\GeneratePlaceholder::class],
        AssetReuploaded::class => [Listeners\GeneratePlaceholder::class],
    ];

    protected $fieldtypes = [
        Fieldtypes\PlaceholderImage::class,
    ];

    protected $tags = [
        Tags\PlaceholderTags::class,
    ];

    protected $vite = [
        'input' => [
            'resources/css/addon.css',
            'resources/js/addon.js',
        ],
        'publicDirectory' => 'resources/dist',
    ];

    public function register()
    {
        $this->registerServices();
        $this->registerAddonConfig();
    }

    protected function registerServices()
    {
        $this->app->singleton(PlaceholderProviders::class, PlaceholderProviders::class);
        $this->app->singleton(PlaceholderService::class, PlaceholderService::class);
    }

    protected function registerAddonConfig()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/placeholders.php', 'mux');

        $this->publishes([
            __DIR__.'/../config/placeholders.php' => config_path('statamic/placeholders.php'),
        ], 'statamic-placeholder-images-config');
    }
}
