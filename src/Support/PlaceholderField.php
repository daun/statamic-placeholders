<?php

namespace Daun\StatamicPlaceholders\Support;

use Daun\StatamicPlaceholders\Fieldtypes\PlaceholderFieldtype;
use Statamic\Assets\Asset;
use Statamic\Assets\AssetContainer;
use Statamic\Fields\Field;

class PlaceholderField
{
    public static function supportsAssetType(Asset $asset): bool
    {
        return $asset->isImage() && ! $asset->isSvg();
    }

    public static function generatesOnUpload(): bool
    {
        return (bool) config('placeholders.generate_on_upload', true);
    }

    public static function existsInBlueprint(Asset|AssetContainer $asset): bool
    {
        return (bool) static::getFromBlueprint($asset);
    }

    public static function assertExistsInBlueprint(Asset $asset): bool
    {
        if (static::existsInBlueprint($asset)) {
            return true;
        } else {
            throw new \Exception('This asset does not have a placeholder field in its blueprint.');
        }
    }

    public static function getFromBlueprint(Asset|AssetContainer|null $asset): ?Field
    {
        return $asset?->blueprint()->fields()->all()->first(
            fn (Field $field) => $field->type() === PlaceholderFieldtype::handle()
        );
    }

    public static function getProvider(Asset|AssetContainer|null $asset): ?string
    {
        if ($field = static::getFromBlueprint($asset)) {
            return $field->fieldtype()->provider();
        } else {
            return null;
        }
    }
}
