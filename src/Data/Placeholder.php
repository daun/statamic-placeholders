<?php

namespace Daun\StatamicPlaceholders\Data;

use Daun\StatamicPlaceholders\Facades\Placeholders;
use Daun\StatamicPlaceholders\Jobs\GeneratePlaceholderJob;
use Daun\StatamicPlaceholders\Support\PlaceholderField;
use Illuminate\Support\Number;
use Statamic\Contracts\Assets\Asset;
use Statamic\Fields\Fieldtype;

class Placeholder
{
    protected ?string $provider;

    public function __construct(
        protected string $hash,
        protected string $blob
    ) {

    }

    public static function make(Asset|string|null $asset): self
    {
        return match(true) {
            $asset instanceof Asset => static::fromAsset($asset),
            is_string($asset) => static::fromUrl($asset),
            default => static::fromBlob($asset),
        };
    }

    public static function fromBlob(string $blob): self
    {
        return new self($blob);
    }

    public static function fromAsset(Asset $asset): self
    {
        return new self($asset->contents());
    }

    public static function fromUrl(string $url): self
    {
        $blob = @file_get_contents($url);
        return new self($blob);
    }

    public function usingProvider(?string $provider = null): self
    {
        $this->provider = $provider;

        return $this;
    }

    public function hash(): ?string
    {
        return Placeholders::hash($this->blob, $this->provider);
    }
}
