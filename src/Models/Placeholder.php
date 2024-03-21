<?php

namespace Daun\StatamicPlaceholders\Models;

use Daun\StatamicPlaceholders\Contracts\PlaceholderProvider;
use Daun\StatamicPlaceholders\Facades\Placeholders;

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
    final public function usingProvider(?string $provider = null): self
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * Get the placeholder provider instance to use.
     */
    final public function provider(): PlaceholderProvider
    {
        return Placeholders::providers()->findOrFail($this->provider);
    }

    /**
     * Get the placeholder type, i.e. the placeholder provider's handle.
     */
    final public function type(): string
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
    final public function generates(): bool
    {
        return Placeholders::enabled();
    }

    /**
     * Get a data uri for this placeholder.
     */
    final public function uri(): string
    {
        return $this->provider()->decode($this->hash()) ?? $this->fallback();
    }

    /**
     * Get the placeholder hash. Generates and saves if not found.
     */
    final public function hash(): ?string
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
    final public function generate(bool $force = false): ?string
    {
        $shouldGenerate = $this->generates() && (! $this->exists() || $force);
        if ($shouldGenerate) {
            $hash = $this->encode();
            $this->save($hash);

            return $hash;
        } else {
            return $this->load();
        }
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
    final protected function encode(): ?string
    {
        if ($contents = $this->contents()) {
            return $this->provider()->encode($contents);
        } else {
            return null;
        }
    }
}
