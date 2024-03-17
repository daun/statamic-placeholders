<?php

namespace Daun\StatamicPlaceholders;

use Daun\StatamicPlaceholders\Contracts\PlaceholderProvider;
use Illuminate\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Statamic\Assets\Asset;
use Statamic\Facades\AssetContainer;

class PlaceholderService
{
    protected PlaceholderProvider $provider;

    protected string $defaultProvider = 'thumbhash';

    protected array $providers = [
        Providers\None::class,
        Providers\AverageColor::class,
        Providers\Blurhash::class,
        Providers\Thumbhash::class,
    ];

    protected array $containers = [];

    public function __construct(
        protected Application $app
    ) {
        $this->provider = $this->makeProvider();
        $this->containers = $this->containers();
    }

    /**
     * Get data uri of placeholder for given asset or url.
     */
    public function uri(Asset|string|null $asset): ?string
    {
        if ($asset && is_string($asset)) {
            $uri = $this->getDataUriForUrl($asset);
        } elseif ($asset && $asset instanceof Asset) {
            $uri = $this->getDataUriForAsset($asset);
        }

        return $uri ?? $this->fallback();
    }

    /**
     * Get placeholder hash for given asset or url.
     */
    public function hash(Asset|string $asset): ?string
    {
        if (is_string($asset) && $asset) {
            return $this->getHashForUrl($asset);
        } elseif ($asset instanceof Asset) {
            return $this->getHashForAsset($asset);
        } else {
            return null;
        }
    }

    /**
     * Check if a  placeholder exists for given asset or url.
     */
    public function exists(Asset|string $asset): bool
    {
        if (is_string($asset) && $asset) {
            return (bool) $this->getHashForUrl($asset);
        } elseif ($asset instanceof Asset) {
            return (bool) $this->getHashForAsset($asset);
        } else {
            return false;
        }
    }

    /**
     * Generate placeholder hash for given asset or url.
     */
    public function generate(Asset|string $asset): ?string
    {
        if (is_string($asset) && $asset) {
            $uri = $this->getDataUriForUrl($asset);
        } elseif ($asset instanceof Asset) {
            $uri = $this->getDataUriForAsset($asset);
        }

        return $uri ?? $this->fallback();
    }

    /**
     * Get data uri of placeholder for given asset.
     */
    protected function getDataUriForAsset(Asset $asset): ?string
    {
        return $this->getDataUriForHash($this->getHashForAsset($asset), $asset->width(), $asset->height());
    }

    /**
     * Get data uri of placeholder for file at given url.
     */
    protected function getDataUriForUrl(string $url): ?string
    {
        return $this->getDataUriForHash($this->getHashForUrl($url));
    }

    /**
     * Get data uri of given placeholder hash.
     */
    protected function getDataUriForHash(?string $hash, int $width = 0, int $height = 0): ?string
    {
        if (! $hash) {
            return null;
        }

        return Cache::rememberForever(
            "asset-placeholder-uri--{$this->provider->name}--{$hash}",
            fn () => $this->provider->decode($hash, $width, $height)
        ) ?: null;
    }

    /**
     * Get placeholder hash for given asset.
     */
    protected function getHashForAsset(Asset $asset): ?string
    {
        if (! $this->supports($asset)) {
            return null;
        }

        if ($hash = $this->loadHashFromAsset($asset, $this->provider->name)) {
            return $hash;
        } else {
            return $this->generateHashForAsset($asset);
        }
    }

    /**
     * Get placeholder hash for file at given url.
     */
    protected function getHashForUrl(string $url): ?string
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $type = $this->provider->name;

        return Cache::rememberForever(
            "asset-placeholder-hash--{$type}--{$url}",
            fn () => $this->generateHashForUrl($url)
        ) ?: null;
    }

    /**
     * Generate and save placeholder hash for given asset.
     */
    protected function generateHashForAsset(Asset $asset): ?string
    {
        if (! $this->supports($asset)) {
            return null;
        }

        if ($hash = $this->generateHashForBlob($asset->contents())) {
            $this->saveHashToAsset($asset, $this->provider->name, $hash);

            return $hash;
        } else {
            return null;
        }
    }

    /**
     * Generate placeholder hash for given url point to a file.
     */
    protected function generateHashForUrl(string $url): ?string
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        if ($contents = @file_get_contents($url)) {
            return $this->generateHashForBlob($contents);
        } else {
            return null;
        }
    }

    /**
     * Generate placeholder hash for given raw file contents.
     */
    protected function generateHashForBlob(string $contents): ?string
    {
        if ($contents) {
            return $this->provider->encode($contents);
        } else {
            return null;
        }
    }

    /**
     * Load placeholder hash from asset metadata.
     */
    protected function loadHashFromAsset(Asset $asset, string $type): ?string
    {
        $data = $asset->get($this->namespace(), []);

        return $data[$type] ?? null;
    }

    /**
     * Save placeholder hash to asset metadata.
     */
    protected function saveHashToAsset(Asset $asset, string $type, string $hash): void
    {
        $data = $asset->get($this->namespace(), []);
        $data[$type] = $hash;
        $asset->set($this->namespace(), $data);
        $asset->saveQuietly();
    }

    /**
     * Handle newly uploaded assets.
     */
    public function handleAssetUpload(Asset $asset): void
    {
        if ($this->config('generate_on_upload')) {
            $this->generateHashForAsset($asset);
        }
    }

    public function supports(Asset $asset): bool
    {
        return $asset->isImage() && ! $asset->isSvg();
    }

    public function valid(Asset $asset): bool
    {
        return
            $this->supports($asset) &&
            in_array($asset->container()->handle(), $this->containers);
    }

    public function namespace(): string
    {
        return $this->config('meta_namespace', 'lqip');
    }

    protected function fallback(): string
    {
        return $this->config('fallback_uri', 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==');
    }

    protected function config(string $key, $default = null): mixed
    {
        return $this->app->config->get("placeholders.{$key}", $default);
    }

    protected function makeProvider(): PlaceholderProvider
    {
        $type = $this->config('placeholder_type', $this->defaultProvider);
        $provider = null;

        if (is_string($type) && class_exists($type)) {
            $provider = $this->app->make($type);
        } elseif (is_string($type) && array_key_exists($type, $this->providers)) {
            $provider = $this->app->make($this->providers[$type]);
        }

        if (is_object($provider) && $provider instanceof PlaceholderProvider) {
            return $provider;
        } else {
            throw new \Exception("Invalid placeholder provider: {$type}");
        }
    }

    public function containers(): array
    {
        $allowed = $this->config('containers', '*');
        if (! $allowed) {
            return [];
        }

        if ($allowed === '*' || $allowed === true) {
            return AssetContainer::all()->map->handle()->all();
        } else {
            return Arr::wrap($allowed);
        }
    }
}
