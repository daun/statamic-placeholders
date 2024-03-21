<?php

namespace Daun\StatamicPlaceholders\Providers;

use Daun\StatamicPlaceholders\Contracts\PlaceholderProvider;

class AverageColor extends PlaceholderProvider
{
    public static string $handle = 'average';

    public static string $name = 'Average Color';

    public function encode(string $contents): ?string
    {
        try {
            $thumb = $this->thumb($contents);
            $rgba = $this->calculateAverage($thumb);

            return $this->rgbaToHex($rgba);
        } catch (\Exception $e) {
            throw new \Exception("Error encoding average color: {$e->getMessage()}");
        }
    }

    public function decode(string $hex, int $width = 0, int $height = 0): ?string
    {
        if (! $hex) {
            return null;
        }

        $rgba = $this->hexToRgba($hex);
        if (count($rgba) < 3) {
            return null;
        }

        return $this->rgbaToDataUri($rgba);
    }

    protected function calculateAverage(?string $contents): array
    {
        if ($contents) {
            $pixel = $this->manager->make($contents)->resize(1, 1);
            $color = $pixel->pickColor(0, 0);
            $pixel->destroy();

            return $color;
        } else {
            return [];
        }
    }

    protected function rgbaToDataUri(array $rgba): string
    {
        return (string) $this->manager->canvas(1, 1, $rgba)->encode('data-url');
    }

    protected function rgbaToHex(array $rgba): string
    {
        return vsprintf('#%02X%02X%02X%02X', $rgba);
    }

    protected function hexToRgba(string $hex): array
    {
        $hex = ltrim($hex, '#');
        $channels = array_map(
            fn ($c) => hexdec(str_pad($c, 2, $c)),
            str_split($hex, strlen($hex) > 3 ? 2 : 1)
        );

        return array_pad($channels, 4, 255);
    }
}
