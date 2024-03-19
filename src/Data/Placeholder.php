<?php

namespace Daun\StatamicPlaceholders\Data;

use Daun\StatamicPlaceholders\Contracts\PlaceholderProvider;
use Daun\StatamicPlaceholders\Facades\Placeholders;
use Illuminate\Support\Str;
use Statamic\Contracts\Assets\Asset;

abstract class Placeholder
{
    protected ?string $provider;

    public static function make(Asset|string|null $input): self
    {
        return match(true) {
            $input instanceof Asset => new AssetPlaceholder($input),
            Str::isUrl($input, ['http', 'https']) => new UrlPlaceholder($input),
            is_string($input) && strlen($input) => new Placeholder($input),
            default => new EmptyPlaceholder()
        };
    }

    public function usingProvider(?string $provider = null): self
    {
        $this->provider = $provider;

        return $this;
    }

    public function provider(): PlaceholderProvider
    {
        return Placeholders::providers()->findOrFail($this->provider);
    }

    abstract public function contents(): ?string;

    public function hash(): ?string
    {
        if ($contents = $this->contents()) {
            return $this->provider()->encode($contents);
        } else {
            return null;
        }
    }

    public function uri(): string
    {
        return $this->provider()->decode($this->hash()) ?? static::fallback();
    }

    public static function fallback(): string
    {
        return (string) config('placeholders.fallback_uri', '');
    }
}
