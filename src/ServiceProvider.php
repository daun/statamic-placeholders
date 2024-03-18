<?php

namespace Daun\StatamicPlaceholders;

use Daun\StatamicPlaceholders\Services\PlaceholderProviderService;
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
        Tags\PlaceholderTag::class,
    ];

    public function register()
    {
        $this->app->singleton(PlaceholderProviderService::class, PlaceholderProviderService::class);
        $this->app->singleton(PlaceholderService::class, PlaceholderService::class);
    }
}
