<?php

namespace Daun\StatamicPlaceholders\Providers;

use Daun\StatamicPlaceholders\Contracts\PlaceholderProvider;
use Daun\StatamicPlaceholders\Support\Intervention;
use Intervention\Image\Colors\Rgb\Colorspace as ColorspaceRgb;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
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
        $driver = $this->service->driverInstance();

        return match (true) {
            $driver instanceof GdDriver => extract_size_and_pixels_with_gd($contents),
            $driver instanceof ImagickDriver => extract_size_and_pixels_with_imagick($contents),
            default => $this->extractSizeAndPixelsWithIntervention($contents),
        };
    }

    /**
     * Driver-agnostic fallback for drivers without a native helper, e.g. libvips.
     */
    protected function extractSizeAndPixelsWithIntervention(string $contents): array
    {
        $image = $this->service->make($contents);
        $width = $image->width();
        $height = $image->height();

        $pixels = [];
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $color = Intervention::toColorspace(Intervention::colorAt($image, $x, $y), ColorspaceRgb::class);
                array_push($pixels, ...array_slice(Intervention::colorChannels($color), 0, 4));
            }
        }

        return [$width, $height, $pixels];
    }
}
