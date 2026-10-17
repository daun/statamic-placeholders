<?php

namespace Daun\StatamicPlaceholders\Providers;

use Daun\StatamicPlaceholders\Contracts\PlaceholderProvider;
use Daun\StatamicPlaceholders\Support\Dimensions;
use Daun\StatamicPlaceholders\Support\Intervention;
use Intervention\Image\Interfaces\ImageInterface;
use kornrunner\Blurhash\Blurhash as BlurhashLib;

class BlurHash extends PlaceholderProvider
{
    public static string $handle = 'blurhash';

    public static string $name = 'BlurHash';

    protected int $compX = 4;

    protected int $compY = 3;

    protected int $calcSize = 50;

    public function encode(string $contents): ?string
    {
        try {
            $pixels = $this->extractPixels($this->thumbnail($contents));
        } catch (\Throwable $th) {
        }

        if (! count($pixels ?? [])) {
            return null;
        }

        try {
            return BlurhashLib::encode($pixels, $this->compX, $this->compY);
        } catch (\Exception $e) {
            throw new \Exception("Error encoding blurhash: {$e->getMessage()}");
        }
    }

    public function decode(string $hash, int $width = 0, int $height = 0): ?string
    {
        if (! $hash) {
            return null;
        }

        [$width, $height] = Dimensions::contain($width, $height, $this->calcSize);

        try {
            $pixels = BlurhashLib::decode($hash, $width, $height);
        } catch (\Exception $e) {
            throw new \Exception("Error decoding blurhash: {$e->getMessage()}");
        }

        return $this->recreateImage($pixels, $width, $height);
    }

    /**
     * Extract the pixels of an image as rows of RGB values: [[[r, g, b], [r, g, b], ...], ...]
     */
    protected function extractPixels(ImageInterface $image): array
    {
        $width = $image->width();
        $height = $image->height();
        $rgba = $this->service->pixels($image);

        $pixels = [];
        for ($y = 0, $i = 0; $y < $height; $y++) {
            $row = [];
            for ($x = 0; $x < $width; $x++, $i += 4) {
                $row[] = [$rgba[$i], $rgba[$i + 1], $rgba[$i + 2]];
            }
            $pixels[] = $row;
        }

        return $pixels;
    }

    /**
     * Recreate an image from rows of RGB values and return it as data uri.
     */
    protected function recreateImage(array $pixels, int $width, int $height): ?string
    {
        if (! count($pixels)) {
            return null;
        }

        $rgb = [];
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                [$r, $g, $b] = $pixels[$y][$x];
                $rgb[] = max(0, min(255, (int) $r));
                $rgb[] = max(0, min(255, (int) $g));
                $rgb[] = max(0, min(255, (int) $b));
            }
        }

        $image = $this->service->drawPixels($this->service->create($width, $height), $rgb);

        return Intervention::toDataUri(Intervention::encodeByExtension($image, 'png'));
    }
}
