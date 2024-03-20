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
     * Whether the placeholder feature is enabled.
     */
    public static function enabled(): bool
    {
        return (bool) config('placeholders.enabled', true);
    }

    /**
     * Get an instance of the service managing placeholder providers.
     */
    public function providers(): PlaceholderProviders
    {
        return $this->providers;
    }

    /**
     * Make a new placeholder object from the given asset, url or file blob.
     */
    public function make(mixed $input): Placeholder
    {
        return Placeholder::make($input);
    }

    /**
     * Get a placeholder data uri for the given asset, url or file blob.
     * Ready to use as image source.
     */
    public function uri(mixed $input, ?string $provider = null): ?string
    {
        return $this->make($input)->usingProvider($provider)->uri();
    }

    /**
     * Get a placeholder hash for the given asset, url or file blob.
     * Short internal representation of a placeholder for efficient storage.
     */
    public function hash(mixed $input, ?string $provider = null): ?string
    {
        return $this->make($input)->usingProvider($provider)->hash();
    }

    /**
     * Get the type of placeholder of the given asset, url or file blob.
     */
    public function type(mixed $input): string
    {
        return $this->make($input)->type();
    }

    /**
     * Check if a placeholder exists for the given asset, url or file blob.
     */
    public function exists(mixed $input, ?string $provider = null): bool
    {
        return $this->make($input)->usingProvider($provider)->exists();
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
