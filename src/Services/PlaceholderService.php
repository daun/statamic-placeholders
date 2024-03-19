<?php

namespace Daun\StatamicPlaceholders\Services;

use Daun\StatamicPlaceholders\Jobs\GeneratePlaceholderJob;
use Daun\StatamicPlaceholders\Models\Placeholder;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Statamic\Assets\Asset;

class PlaceholderService
{
    public function __construct(
        protected PlaceholderProviders $providers,
        protected Application $app,
        protected Repository $config
    ) {
    }

    /**
     * Get an instance of the service managing placeholder providers.
     */
    public function providers(): PlaceholderProviders
    {
        return $this->providers;
    }

    /**
     * Make a new placeholder object.
     */
    public function make(Asset|string|null $input): Placeholder
    {
        return Placeholder::make($input);
    }

    /**
     * Check if a placeholder exists for given asset or url.
     */
    public function exists(Asset|string $asset, ?string $provider = null): bool
    {
        return $this->make($asset)->usingProvider($provider)->exists();
    }

    /**
     * Generate a placeholder for the given asset or url. Returns the generated hash.
     */
    public function generate(Asset $asset): ?string
    {
        return $this->make($asset)->generate();
    }

    /**
     * Dispatch an asynchronous job to generate a placeholder for the given asset.
     */
    public function dispatch(Asset $asset): void
    {
        GeneratePlaceholderJob::dispatch($asset);
    }
}
