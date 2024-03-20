<?php

namespace Daun\StatamicPlaceholders\Models;

use Daun\StatamicPlaceholders\Support\PlaceholderData;
use Daun\StatamicPlaceholders\Support\PlaceholderField;
use Statamic\Contracts\Assets\Asset;

/**
 * A placeholder for an image asset.
 * Reads and writes the placeholder hash to the asset's metadata.
 */
class AssetPlaceholder extends Placeholder
{
    public function __construct(protected Asset $asset)
    {
        $this->provider = PlaceholderField::getProvider($asset);
    }

    public static function accepts(mixed $input): bool
    {
        return ($input instanceof Asset) && PlaceholderField::ensureEnabledForAsset($input);
    }

    protected function load(): ?string
    {
        return PlaceholderData::getHash($this->asset, $this->provider()::$handle);
    }

    protected function save(string $hash): void
    {
        PlaceholderData::addHash($this->asset, $hash, $this->provider()::$handle);
    }

    public function contents(): ?string
    {
        return $this->asset->contents();
    }
}
