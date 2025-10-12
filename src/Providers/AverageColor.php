<?php

namespace Daun\StatamicPlaceholders\Providers;

use Daun\StatamicPlaceholders\Contracts\PlaceholderProvider;
use Daun\StatamicPlaceholders\Support\Color;

class AverageColor extends PlaceholderProvider
{
    public static string $handle = 'average';

    public static string $name = 'Average Color';

    public function encode(string $contents): ?string
    {
        try {
            $thumb = $this->thumb($contents);
            return $this->calculateAverage($thumb);
        } catch (\Exception $e) {
            throw new \Exception("Error encoding average color: {$e->getMessage()}");
        }
    }

    public function decode(string $hex, int $width = 0, int $height = 0): ?string
    {
        if (! $hex) {
            return null;
        }

        $rgba = Color::hexToRgba($hex);
        if (count($rgba) < 3) {
            return null;
        }

        return $this->rgbaToDataUri($rgba);
    }

    protected function calculateAverage(?string $contents): ?string
    {
        if (! $contents) {
            return null;
        }

        return $this->service->manager()->read($contents)
            ->resize(1, 1)
            ->pickColor(0, 0)
            ->toHex();
    }

    protected function rgbaToDataUri(array $rgba): string
    {
        return $this->service->manager()
            ->create(1, 1)
            ->fill($rgba)
            ->toPng()
            ->toDataUri();
    }
}
