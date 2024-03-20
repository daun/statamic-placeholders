<?php

namespace Daun\StatamicPlaceholders\Support;

use Statamic\Assets\Asset;

class PlaceholderData
{
    public static function load(Asset $asset): array
    {
        if ($field = PlaceholderField::getFromBlueprint($asset)) {
            return $asset->get($field->handle(), []);
        } else {
            return [];
        }
    }

    public static function save(Asset $asset, ?array $data): void
    {
        if ($field = PlaceholderField::getFromBlueprint($asset)) {
            $asset->set($field->handle(), $data);
            $asset->saveQuietly();
        }
    }

    public static function clear(Asset $asset): void
    {
        static::save($asset, []);
    }

    public static function getHash(Asset $asset, string $provider): ?string
    {
        return static::load($asset)[$provider] ?? null;
    }

    public static function addHash(Asset $asset, string $hash, string $provider): void
    {
        $data = static::load($asset);
        $data[$provider] = $hash;
        static::save($asset, $data);
    }
}
