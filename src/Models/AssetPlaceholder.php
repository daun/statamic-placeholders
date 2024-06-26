<?php

namespace Daun\StatamicPlaceholders\Models;

use Daun\StatamicPlaceholders\Support\PlaceholderData;
use Daun\StatamicPlaceholders\Support\PlaceholderField;
use Statamic\Assets\Asset;

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
        return ($input instanceof Asset)
            && PlaceholderField::supportsAssetType($input)
            && PlaceholderField::assertExistsInBlueprint($input);
    }

    protected function load(): ?string
    {
        return PlaceholderData::getHash($this->asset, $this->provider()::$handle);
    }

    protected function save(?string $hash): void
    {
        PlaceholderData::addHash($this->asset, $hash, $this->provider()::$handle);
    }

    public function delete(): void
    {
        PlaceholderData::clear($this->asset);
    }

    public function contents(): ?string
    {
        return $this->asset->contents();
    }

    protected function width(): int
    {
        return $this->asset->width();
    }

    protected function height(): int
    {
        return $this->asset->height();
    }
}
