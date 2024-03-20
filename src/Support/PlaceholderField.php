<?php

namespace Daun\StatamicPlaceholders\Support;

use Daun\StatamicPlaceholders\Fieldtypes\PlaceholderFieldtype;
use Illuminate\Support\Collection;
use Statamic\Assets\Asset;
use Statamic\Assets\AssetContainer;
use Statamic\Facades\AssetContainer as AssetContainerFacade;
use Statamic\Fields\Field;

class PlaceholderField
{
    public static function containers(): Collection
    {
        return AssetContainerFacade::all()->filter(
            fn (AssetContainer $container) => static::enabledForContainer($container)
        );
    }

    public static function enabledForAsset(Asset $asset): bool
    {
        return $asset->isImage() && ! $asset->isSvg() && static::hasField($asset);
    }

    public static function ensureEnabledForAsset(Asset $asset): bool
    {
        if (static::enabledForAsset($asset)) {
            return true;
        } else {
            throw new \Exception('This asset does not have a placeholder field in its blueprint.');
        }
    }

    public static function enabledForContainer(AssetContainer $container): bool
    {
        return static::hasField($container);
    }

    public static function generatesOnUpload(): bool
    {
        return (bool) config('placeholders.generate_on_upload', true);
    }

    public static function hasField(Asset|AssetContainer $asset): bool
    {
        return (bool) static::getField($asset);
    }

    public static function getField(Asset|AssetContainer|null $asset): ?Field
    {
        return $asset?->blueprint()->fields()->all()->first(
            fn (Field $field) => $field->type() === PlaceholderFieldtype::handle()
        );
    }

    public static function getProvider(Asset|AssetContainer|null $asset): ?string
    {
        if ($field = static::getField($asset)) {
            return $field->fieldtype()->provider();
        } else {
            return null;
        }
    }
}
