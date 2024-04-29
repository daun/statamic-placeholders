<?php

namespace Daun\StatamicPlaceholders\Models;

use Daun\StatamicPlaceholders\Contracts\PlaceholderProvider;
use Daun\StatamicPlaceholders\Facades\Placeholders;
use Daun\StatamicPlaceholders\Services\ImageManager;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Exception\NotSupportedException;

/**
 * Abstract placeholder class.
 * Manages placeholder generation and storage.
 */
abstract class Placeholder
{
    protected static array $types = [
        AssetPlaceholder::class,
        UrlPlaceholder::class,
        BlobPlaceholder::class,
    ];

    protected ?string $provider = null;

    protected ?string $hash = null;

    protected ?string $uri = null;

    /**
     * Create a new placeholder instance from an asset, url or blob.
     */
    public static function make(mixed $input): self
    {
        $class = collect(static::$types)->first(
            fn ($type) => $type::accepts($input),
            EmptyPlaceholder::class
        );

        return new $class($input);
    }

    /**
     * Set the placeholder provider to use.
     */
    public function usingProvider(?string $provider = null): self
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * Get the placeholder provider instance to use.
     */
    public function provider(): PlaceholderProvider
    {
        return Placeholders::providers()->findOrFail($this->provider);
    }

    /**
     * Get the placeholder type, i.e. the placeholder provider's handle.
     */
    public function type(): string
    {
        return $this->provider()::$handle;
    }

    /**
     * Whether this placeholder accepts the given input as a valid source.
     */
    abstract public static function accepts(mixed $input): bool;

    /**
     * Whether this placeholder should generate missing placeholders.
     */
    public function generates(): bool
    {
        return Placeholders::enabled();
    }

    /**
     * Get a data uri for this placeholder.
     */
    public function uri(?string $format = null): string
    {
        if ($hash = $this->hash()) {
            if ($uri = $this->decode($hash, $format)) {
                return $uri;
            }
        }

        return $this->fallback();
    }

    /**
     * Get the placeholder hash. Generates and saves if not found.
     */
    public function hash(): ?string
    {
        return $this->load() ?: $this->generate() ?: null;
    }

    /**
     * Get the fallback uri to use if no placeholder exists.
     */
    public function fallback(): string
    {
        return (string) config('placeholders.fallback_uri', '');
    }

    /**
     * Check if a placeholder exists.
     */
    public function exists(): bool
    {
        return (bool) $this->load();
    }

    /**
     * Generate and return the placeholder hash.
     */
    public function generate(bool $force = false): ?string
    {
        $shouldGenerate = $this->generates() && (! $this->exists() || $force);
        if (! $shouldGenerate) {
            return $this->load();
        }

        if ($contents = $this->contents()) {
            $hash = $this->encode($contents);
            $this->save($hash);
        }

        return $hash ?? null;
    }

    /**
     * Get the blob contents of this placeholder.
     */
    abstract protected function contents(): ?string;

    /**
     * Load the hash from cache or storage.
     */
    protected function load(): ?string
    {
        /* not implemented */
        return null;
    }

    /**
     * Save the hash to cache or storage.
     */
    protected function save(?string $hash): void
    {
        /* not implemented */
    }

    /**
     * Delete the hash from cache or storage.
     */
    public function delete(): void
    {
        /* not implemented */
    }

    /**
     * Convert the blob contents to a placeholder hash.
     */
    protected function encode(string $contents): ?string
    {
        return $this->provider()->encode($contents);
    }

    /**
     * Convert the placeholder hash to a data uri.
     */
    protected function decode(string $hash, ?string $format = null): ?string
    {
        $format = $format ?? config('placeholders.uri_format', 'png');

        $uri = Cache::rememberForever(
            "placeholder-uri--{$this->type()}--{$format}--{$hash}",
            function () use ($hash, $format) {
                $png = $this->provider()->decode($hash, $this->width(), $this->height());

                return $png ? $this->compress($png, $format) : null;
            }
        );

        return $uri ?: null;
    }

    /**
     * Compress and resize a data uri.
     */
    protected function compress(string $contents, ?string $format = null): string
    {
        $manager = app()->make(ImageManager::class);

        /** @var \Intervention\Image\Image */
        try {
            $base = $manager->make($contents);
        } catch (\Throwable $th) {
            // not a valid image? return uncompressed
            return $contents;
        }

        try {
            switch ($format) {
                case 'webp':
                    $compressed = $manager->fit($base, 32)->encode('webp');
                    $compressed->mime = 'image/webp';
                    break;
                case 'avif':
                    $compressed = $manager->fit($base, 32)->encode('avif');
                    $compressed->mime = 'image/avif';
                    break;
                default:
                    $compressed = $manager->fit($base, 16)->encode('png');
                    break;
            }
        } catch (NotSupportedException $th) {
            $compressed = $manager->fit($base, 16)->encode('png');
        }

        $result = (string) $compressed->encode('data-url');
        $compressed->destroy();

        return $result;
    }

    /**
     * Width of the original image.
     */
    protected function width(): int
    {
        return 0;
    }

    /**
     * Height of the original image.
     */
    protected function height(): int
    {
        return 0;
    }
}
