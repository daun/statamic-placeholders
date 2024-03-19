<?php

namespace Daun\StatamicPlaceholders\Services;

use Daun\StatamicPlaceholders\Jobs\GeneratePlaceholderJob;
use Daun\StatamicPlaceholders\Support\PlaceholderImageFieldtype;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Cache;
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
     * Get a placeholder data uri for a given asset or url. Ready to use as image source.
     */
    public function uri(Asset|string|null $asset, ?string $provider = null): ?string
    {
        return match (true) {
            $asset && is_string($asset) => $this->getDataUriForUrl($asset, $provider),
            $asset instanceof Asset => $this->getDataUriForAsset($asset, $provider),
            default => null,
        } ?? $this->fallback();
    }

    /**
     * Get placeholder hash for given asset or url. Short internal representation of a placeholder.
     */
    public function hash(Asset|string $asset, ?string $provider = null): ?string
    {
        return match (true) {
            $asset && is_string($asset) => $this->getHashForUrl($asset, $provider),
            $asset instanceof Asset => $this->getHashForAsset($asset, $provider),
            default => null,
        };
    }

    /**
     * Check if a placeholder exists for given asset or url.
     */
    public function exists(Asset|string $asset, ?string $provider = null): bool
    {
        return (bool) match (true) {
            $asset && is_string($asset) => $this->loadHashForUrl($asset, $provider),
            $asset instanceof Asset => $this->loadHashFromAsset($asset, $provider),
            default => null,
        };
    }

    /**
     * Generate a placeholder for the given asset. Returns the generated hash.
     */
    public function generate(Asset $asset, ?string $provider = null): ?string
    {
        return match (true) {
            $asset && is_string($asset) => $this->generateHashForUrl($asset, $provider),
            $asset instanceof Asset => $this->generateHashForAsset($asset, $provider),
            default => null,
        };
    }

    /**
     * Dispatch an asynchronous job to generate a placeholder for the given asset.
     */
    public function dispatch(Asset $asset, ?string $provider = null): void
    {
        GeneratePlaceholderJob::dispatch($asset, $provider);
    }

    /**
     * Get data uri of placeholder for given asset.
     */
    protected function getDataUriForAsset(Asset $asset, ?string $provider = null): ?string
    {
        return $this->getDataUriForHash(
            $this->getHashForAsset($asset, $provider),
            width: $asset->width(),
            height: $asset->height(),
            provider: $provider
        );
    }

    /**
     * Get data uri of placeholder for file at given url.
     */
    protected function getDataUriForUrl(string $url, ?string $provider = null): ?string
    {
        return $this->getDataUriForHash(
            $this->getHashForUrl($url, $provider),
            provider: $provider
        );
    }

    /**
     * Get data uri of given placeholder hash.
     */
    protected function getDataUriForHash(?string $hash, int $width = 0, int $height = 0, ?string $provider = null): ?string
    {
        if (! $hash) {
            return null;
        }

        $instance = $this->providers->findOrFail($provider);

        return Cache::rememberForever(
            "asset-placeholder-uri--{$instance::$handle}--{$hash}",
            fn () => $instance->decode($hash, $width, $height)
        ) ?: null;
    }

    /**
     * Get placeholder hash for given asset.
     */
    protected function getHashForAsset(Asset $asset, ?string $provider = null): ?string
    {
        if (! $this->supports($asset)) {
            return null;
        }

        $provider ??= PlaceholderImageFieldtype::getPlaceholderProvider($asset);
        $instance = $this->providers->findOrFail($provider);

        if ($hash = $this->loadHashFromAsset($asset, $instance::$handle)) {
            return $hash;
        } else {
            return $this->generateHashForAsset($asset, $instance::$handle);
        }
    }

    /**
     * Get placeholder hash for file at given url.
     */
    protected function getHashForUrl(string $url, ?string $provider = null): ?string
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $instance = $this->providers->findOrFail($provider);

        if ($hash = $this->loadHashForUrl($url, $instance::$handle)) {
            return $hash;
        } else {
            $hash = $this->generateHashForUrl($url, $instance::$handle);
            Cache::set("asset-placeholder-hash--{$instance::$handle}--{$url}", $hash);
        }
    }

    /**
     * Generate and save placeholder hash for given asset.
     */
    protected function generateHashForAsset(Asset $asset, ?string $provider = null): ?string
    {
        if (! $this->supports($asset)) {
            return null;
        }

        $provider ??= PlaceholderImageFieldtype::getPlaceholderProvider($asset);
        $instance = $this->providers->findOrFail($provider);

        if ($hash = $this->generateHashForBlob($asset->contents(), $instance::$handle)) {
            $this->saveHashToAsset($asset, $hash, $instance::$handle);

            return $hash;
        } else {
            return null;
        }
    }

    /**
     * Generate placeholder hash for given url point to a file.
     */
    protected function generateHashForUrl(string $url, ?string $provider = null): ?string
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        if ($contents = @file_get_contents($url)) {
            return $this->generateHashForBlob($contents, $provider);
        } else {
            return null;
        }
    }

    /**
     * Generate placeholder hash for given raw file contents.
     */
    protected function generateHashForBlob(string $contents, ?string $provider = null): ?string
    {
        $instance = $this->providers->findOrFail($provider);

        if ($contents) {
            return $instance->encode($contents);
        } else {
            return null;
        }
    }

    /**
     * Load placeholder hash from cache for given url.
     */
    protected function loadHashForUrl(string $url, ?string $provider = null): ?string
    {
        $instance = $this->providers->findOrFail($provider);

        return Cache::get("asset-placeholder-hash--{$instance::$handle}--{$url}");
    }

    /**
     * Load placeholder hash from asset metadata.
     */
    protected function loadHashFromAsset(Asset $asset, ?string $provider = null): ?string
    {
        $provider ??= PlaceholderImageFieldtype::getPlaceholderProvider($asset);
        $instance = $this->providers->findOrFail($provider);

        return PlaceholderImageFieldtype::getPlaceholderHash($asset, $instance::$handle);
    }

    /**
     * Save placeholder hash to asset metadata.
     */
    protected function saveHashToAsset(Asset $asset, string $hash, ?string $provider = null): void
    {
        $instance = $this->providers->findOrFail($provider);
        PlaceholderImageFieldtype::addPlaceholderHash($asset, $hash, $instance::$handle);
    }

    protected function fallback(): string
    {
        return (string) $this->config->get('placeholders.fallback_uri', '');
    }

    public function supports(Asset $asset): bool
    {
        return PlaceholderImageFieldtype::enabledForAsset($asset);
    }

    public function containers(): array
    {
        return PlaceholderImageFieldtype::containers()->all();
    }
}
