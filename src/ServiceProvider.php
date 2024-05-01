<?php

namespace Daun\StatamicPlaceholders;

use Daun\StatamicPlaceholders\Services\ImageManager;
use Daun\StatamicPlaceholders\Services\PlaceholderProviders;
use Daun\StatamicPlaceholders\Services\PlaceholderService;
use Statamic\Events\AssetReuploaded;
use Statamic\Events\AssetUploaded;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    protected $commands = [
        Commands\GenerateCommand::class,
        Commands\ClearCommand::class,
    ];

    protected $listen = [
        AssetUploaded::class => [
            Listeners\GeneratePlaceholder::class,
        ],
        AssetReuploaded::class => [
            Listeners\GeneratePlaceholder::class,
        ],
    ];

    protected $fieldtypes = [
        Fieldtypes\PlaceholderFieldtype::class,
    ];

    protected $tags = [
        Tags\PlaceholderTag::class,
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
        $this->app->singleton(PlaceholderProviders::class);
        $this->app->singleton(PlaceholderService::class);
        $this->app->singleton(ImageManager::class);
    }

    public function bootAddon(): void
    {
        $this->autoPublishConfig();
    }

    /**
     * Modified version of parent `bootConfig` method to customize
     * the config file name.
     */
    protected function bootConfig()
    {
        $filename = 'placeholders';
        $directory = $this->getAddon()->directory();
        $origin = "{$directory}config/{$filename}.php";

        if (! $this->config || ! file_exists($origin)) {
            return $this;
        }

        $this->mergeConfigFrom($origin, $filename);

        $this->publishes([
            $origin => config_path("{$filename}.php"),
        ], "{$filename}-config");

        return parent::bootConfig();
    }

    protected function autoPublishConfig(): self
    {
        Statamic::afterInstalled(function ($command) {
            $command->call('vendor:publish', ['--tag' => 'placeholders-config']);
        });

        return $this;
    }

    public function provides(): array
    {
        return [
            PlaceholderProviders::class,
            PlaceholderService::class,
            ImageManager::class,
        ];
    }
}
