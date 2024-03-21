<?php

namespace Daun\StatamicPlaceholders\Providers;

use Daun\StatamicPlaceholders\Contracts\PlaceholderProvider;
use Thumbhash\Thumbhash as ThumbhashLib;

use function Thumbhash\extract_size_and_pixels_with_gd;
use function Thumbhash\extract_size_and_pixels_with_imagick;

class ThumbHash extends PlaceholderProvider
{
    public static string $handle = 'thumbhash';

    public static string $name = 'ThumbHash';

    public function encode(string $contents): ?string
    {
        try {
            $thumb = $this->thumb($contents);
            [$width, $height, $pixels] = $this->extractSizeAndPixels($thumb);
            $hash = ThumbhashLib::RGBAToHash($width, $height, $pixels);

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

    protected function extractSizeAndPixels(string $contents): array
    {
        return match ($this->manager->driver()) {
            $this->manager::DRIVER_IMAGICK => extract_size_and_pixels_with_imagick($contents),
            $this->manager::DRIVER_GD => extract_size_and_pixels_with_gd($contents),
        };
    }
}
