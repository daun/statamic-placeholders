<?php

namespace Daun\StatamicPlaceholders\Providers;

use Daun\StatamicPlaceholders\Contracts\PlaceholderProvider;
use Thumbhash\Thumbhash as ThumbhashLib;

class ThumbHash extends PlaceholderProvider
{
    public static string $handle = 'thumbhash';

    public static string $name = 'ThumbHash';

    public function encode(string $contents): ?string
    {
        try {
            $thumb = $this->thumbnail($contents);
            $pixels = $this->service->pixels($thumb);
            $hash = ThumbhashLib::RGBAToHash($thumb->width(), $thumb->height(), $pixels);

            return ThumbhashLib::convertHashToString($hash);
        } catch (\Exception $e) {
            throw new \Exception("Error encoding thumbhash: {$e->getMessage()}");
        }
    }

    public function decode(string $hash, int $width = 0, int $height = 0): ?string
    {
        if (! $hash) {
            return null;
        }

        try {
            $hash = ThumbhashLib::convertStringToHash($hash);

            return ThumbhashLib::toDataURL($hash);
        } catch (\Exception $e) {
            throw new \Exception("Error decoding thumbhash: {$e->getMessage()}");
        }
    }
}
