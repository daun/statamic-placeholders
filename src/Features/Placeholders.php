<?php

namespace Daun\StatamicPlaceholders\Features;

use Daun\StatamicPlaceholders\Fieldtypes\PlaceholderImage;
use Illuminate\Support\Collection;
use Statamic\Assets\Asset;
use Statamic\Assets\AssetContainer;
use Statamic\Facades\AssetContainer as AssetContainerFacade;
use Statamic\Fields\Field;

class Placeholders
{
    public static function enabled(): bool
    {
        return config('placeholders.enabled', true);
    }

    public static function enabledForAsset(Asset $asset): bool
    {
        return $asset->isImage() && static::hasPlaceholderField($asset);
    }

    public static function enabledForContainer(AssetContainer $container): bool
    {
        return static::hasPlaceholderField($container);
    }

    public static function shouldGenerate(Asset $asset): bool
    {
        return static::enabled() && static::enabledForAsset($asset);
    }

    public static function hasPlaceholderField(Asset|AssetContainer $asset): bool
    {
        return (bool) static::getPlaceholderField($asset);
    }

    public static function getPlaceholderField(Asset|AssetContainer|null $asset): ?string
    {
        return $asset?->blueprint()->fields()->all()->first(
            fn (Field $field) => $field->type() === PlaceholderImage::handle()
        )?->handle();
    }

    public static function containers(): Collection
    {
        return AssetContainerFacade::all()->filter(
            fn (AssetContainer $container) => static::enabledForContainer($container)
        );
    }
}
