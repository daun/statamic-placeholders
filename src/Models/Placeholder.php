<?php

namespace Daun\StatamicPlaceholders\Models;

use Daun\StatamicPlaceholders\Contracts\PlaceholderProvider;
use Daun\StatamicPlaceholders\Facades\Placeholders;
use Statamic\Contracts\Assets\Asset;

abstract class Placeholder
{
    protected static array $types = [
        AssetPlaceholder::class,
        UrlPlaceholder::class,
        BlobPlaceholder::class,
    ];

    protected ?string $provider;

    protected ?string $hash;

    protected ?string $uri;

    /**
     * Create a new placeholder instance from an asset, url or blob.
     */
    public static function make(Asset|string|null $input): self
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
     * Get the placeholder provider instace to use.
     */
    public function provider(): PlaceholderProvider
    {
        return Placeholders::providers()->findOrFail($this->provider);
    }

    /**
     * Whether this placeholder accepts the given input as a valid source.
     */
    abstract public static function accepts(mixed $input): bool;

    /**
     * Get a data uri for this placeholder.
     */
    public function uri(): string
    {
        return $this->provider()->decode($this->hash()) ?? static::fallback();
    }

    /**
     * Get the placeholder hash. Generates and saves if not found.
     */
    public function hash(): ?string
    {
        if ($hash = $this->load()) {
            return $hash;
        } elseif ($hash = $this->encode()) {
            $this->save($hash);

            return $hash;
        } else {
            return null;
        }
    }

    /**
     * Get the fallback uri to use if no placeholder exists.
     */
    public static function fallback(): string
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
    public function generate(): ?string
    {
        return $this->hash();
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
    protected function save(string $hash): void
    {
        /* not implemented */

    }

    /**
     * Convert the blob contents to a placeholder hash.
     */
    protected function encode(): ?string
    {
        if ($contents = $this->contents()) {
            return $this->provider()->encode($contents);
        } else {
            return null;
        }
    }
}
