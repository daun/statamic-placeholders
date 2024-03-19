<?php

namespace Daun\StatamicPlaceholders\Data;

use Daun\StatamicPlaceholders\Facades\Placeholders;
use Statamic\Contracts\Assets\Asset;

class AssetPlaceholder extends Placeholder
{
    protected ?string $provider;

    public function __construct(
        protected Asset $asset
    ) {
    }

    public function key(): ?string
    {
        return "asset-placeholder-hash--{$this->provider()::$handle}--{$this->url}";
    }

    public function exists(): ?string
    {
        return Cache::has($this->key());
    }

    public function contents(): ?string
    {
        return $this->asset->contents();
    }
}
