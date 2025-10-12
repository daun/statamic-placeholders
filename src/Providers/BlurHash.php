<?php

namespace Daun\StatamicPlaceholders\Providers;

use Daun\StatamicPlaceholders\Contracts\PlaceholderProvider;
use Daun\StatamicPlaceholders\Support\Dimensions;
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
            $thumb = $this->thumb($contents);
            $pixels = $this->extractPixels($thumb);
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

    protected function extractPixels(?string $contents): array
    {
        if (! $contents) {
            return [];
        }

        $image = $this->service->manager()->read($contents);
        $width = $image->width();
        $height = $image->height();

        $pixels = [];
        for ($y = 0; $y < $height; $y++) {
            $row = [];
            for ($x = 0; $x < $width; $x++) {
                [$r, $g, $b] = $image->pickColor($x, $y)->convertTo('rgb')->toArray();
                $row[] = [$r, $g, $b];
            }
            $pixels[] = $row;
        }

        return $pixels;
    }

    protected function recreateImage(array $pixels, int $width, int $height): ?string
    {
        if (! ($pixels && count($pixels))) {
            return null;
        }

        [$width, $height] = Dimensions::contain($width, $height, $this->calcSize);

        $image = $this->service->manager()->create($width, $height);
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                [$r, $g, $b] = $pixels[$y][$x];
                $rgb = [max(0, min(255, $r)), max(0, min(255, $g)), max(0, min(255, $b))];
                $image->drawPixel($x, $y, $rgb);
            }
        }

        return $image->toPng()->toDataUri();
    }
}
