<?php

namespace Daun\StatamicPlaceholders;

use Daun\StatamicPlaceholders\Services\PlaceholderProviders;
use Daun\StatamicPlaceholders\Services\PlaceholderService;
use Statamic\Events\AssetReuploaded;
use Statamic\Events\AssetUploaded;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $commands = [
        Commands\Generate::class,
    ];

    protected $listen = [
        AssetUploaded::class => [Listeners\GeneratePlaceholder::class],
        AssetReuploaded::class => [Listeners\GeneratePlaceholder::class],
    ];

    protected $fieldtypes = [
        Fieldtypes\PlaceholderFieldtype::class,
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
        $this->app->singleton(PlaceholderProviders::class);
        $this->app->singleton(PlaceholderService::class);
    }

    protected function registerAddonConfig()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/placeholders.php', 'placeholders');

        $this->publishes([
            __DIR__.'/../config/placeholders.php' => config_path('placeholders.php'),
        ], 'statamic-placeholders');
    }
}
