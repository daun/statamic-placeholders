<?php

namespace Daun\StatamicPlaceholders\Providers;

use Daun\StatamicPlaceholders\Contracts\PlaceholderProvider;
use Daun\StatamicPlaceholders\Support\Intervention;
use Intervention\Image\Interfaces\ImageInterface;

class AverageColor extends PlaceholderProvider
{
    public static string $handle = 'average';

    public static string $name = 'Average Color';

    public function encode(string $contents): ?string
    {
        try {
            return $this->calculateAverage($this->thumbnail($contents));
        } catch (\Exception $e) {
            throw new \Exception("Error encoding average color: {$e->getMessage()}");
        }
    }

    public function decode(string $hex, int $width = 0, int $height = 0): ?string
    {
        return $hex
            ? $this->toDataUri($hex)
            : null;
    }

    protected function calculateAverage(ImageInterface $image): ?string
    {
        $pixel = $image->resize(1, 1);

        return Intervention::toHex(Intervention::colorAt($pixel, 0, 0), '#');
    }

    protected function toDataUri(string $hex): string
    {
        $pixel = $this->service->create(1, 1)->fill($hex);

        return Intervention::toDataUri(Intervention::encodeByExtension($pixel, 'png'));
    }
}
