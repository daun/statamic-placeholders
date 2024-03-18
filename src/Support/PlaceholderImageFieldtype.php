<?php

namespace Daun\StatamicPlaceholders\Support;

use Daun\StatamicPlaceholders\Fieldtypes\PlaceholderImage as Fieldtype;
use Illuminate\Support\Collection;
use Statamic\Assets\Asset;
use Statamic\Assets\AssetContainer;
use Statamic\Facades\AssetContainer as AssetContainerFacade;
use Statamic\Fields\Field;

class PlaceholderImageFieldtype
{
    public static function enabled(): bool
    {
        return (bool) config('placeholders.enabled', true);
    }

    public static function generatesOnUpload(): bool
    {
        return (bool) config('placeholders.generate_on_upload', true);
    }

    public static function enabledForAsset(Asset $asset): bool
    {
        return $asset->isImage() && ! $asset->isSvg() && static::hasPlaceholderField($asset);
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
            fn (Field $field) => $field->type() === Fieldtype::handle()
        )?->handle();
    }

    public static function containers(): Collection
    {
        return AssetContainerFacade::all()->filter(
            fn (AssetContainer $container) => static::enabledForContainer($container)
        );
    }

    public static function loadPlaceholderData(Asset $asset): array
    {
        $field = static::getPlaceholderField($asset);

        return $asset->get($field, []);
    }

    public static function savePlaceholderData(Asset $asset, ?array $data): void
    {
        $field = static::getPlaceholderField($asset);
        $asset->set($field, $data);
        $asset->saveQuietly();
    }

    public static function clearPlaceholderData(Asset $asset): void
    {
        static::savePlaceholderData($asset, []);
    }

    public static function getPlaceholderHash(Asset $asset, string $provider): ?string
    {
        $data = static::loadPlaceholderData($asset);

        return $data[$provider] ?? null;
    }

    public static function addPlaceholderHash(Asset $asset, string $hash, string $provider): void
    {
        $data = static::loadPlaceholderData($asset);
        $data[$provider] = $hash;
        static::savePlaceholderData($asset, $data);
    }
}
