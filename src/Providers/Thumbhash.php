<?php

namespace Daun\StatamicPlaceholders\Providers;

use Daun\StatamicPlaceholders\Contracts\PlaceholderProvider;
use Daun\StatamicPlaceholders\Support\Dimensions;
use Daun\StatamicPlaceholders\Support\Imagick;
use Thumbhash\Thumbhash as ThumbhashLib;

class Thumbhash implements PlaceholderProvider
{
    public static string $name = 'thumbhash';

    protected int $maxThumbSize = 100;

    public function encode(string $contents): ?string
    {
        try {
            [$width, $height, $pixels] = $this->generatePixelMatrixFromImage($contents);
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

    protected function generatePixelMatrixFromImage(?string $contents): array
    {
        if (! $contents) {
            return [];
        }

        if (Imagick::installed()) {
            return $this->generatePixelMatrixFromImageUsingImagick($contents);
        } else {
            return $this->generatePixelMatrixFromImageUsingGD($contents);
        }
    }

    protected function generatePixelMatrixFromImageUsingGD(string $contents): array
    {
        $image = @imagecreatefromstring($contents);
        [$width, $height] = Dimensions::contain(imagesx($image), imagesy($image), $this->maxThumbSize);
        $image = imagescale($image, $width, $height);

        $pixels = [];
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $color_index = imagecolorat($image, $x, $y);
                $color = imagecolorsforindex($image, $color_index);
                $alpha = 255 - ceil($color['alpha'] * (255 / 127)); // GD only supports 7-bit alpha channel
                $pixels[] = $color['red'];
                $pixels[] = $color['green'];
                $pixels[] = $color['blue'];
                $pixels[] = $alpha;
            }
        }

        return [$width, $height, $pixels];
    }

    protected function generatePixelMatrixFromImageUsingImagick(string $contents): array
    {
        $image = new \Imagick();
        $image->readImageBlob($contents);
        [$width, $height] = Dimensions::contain($image->getImageWidth(), $image->getImageHeight(), $this->maxThumbSize);
        $image->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1);

        $pixels = [];
        foreach ($image->getPixelIterator() as $row) {
            foreach ($row as $pixel) {
                $colors = $pixel->getColor(2);
                $pixels[] = $colors['r'];
                $pixels[] = $colors['g'];
                $pixels[] = $colors['b'];
                $pixels[] = $colors['a'];
            }
        }

        $image->destroy();

        return [$width, $height, $pixels];
    }
}
