<?php

namespace Daun\StatamicPlaceholders\Providers;

use Daun\StatamicPlaceholders\Contracts\PlaceholderProvider;
use Daun\StatamicPlaceholders\Support\Imagick;

class AverageColor implements PlaceholderProvider
{
    public static string $name = 'average';

    public function encode(string $contents): ?string
    {
        try {
            $rgba = $this->calculateAverage($contents);

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

        [$r, $g, $b, $a] = $rgba;

        return $this->rgbaToDataUri($r, $g, $b, $a);
    }

    protected function calculateAverage(?string $contents): array
    {
        if (! $contents) {
            return [];
        }

        if (Imagick::installed()) {
            $image = new \Imagick();
            $image->readImageBlob($contents);
            $image->resizeImage(1, 1, \Imagick::FILTER_LANCZOS, 1);
            $pixel = $image->getImagePixelColor(0, 0);
            $rgba = $pixel->getColor(2);
            $image->destroy();

            return array_slice(array_values($rgba), 0, 4);
        } else {
            $image = @imagecreatefromstring($contents);
            $image = imagescale($image, 1, 1);
            $rgba = imagecolorsforindex($image, imagecolorat($image, 0, 0));
            imagedestroy($image);

            return array_slice(array_values($rgba), 0, 4);
        }
    }

    protected function rgbaToDataUri(int $r, int $g, int $b, int $a): string
    {
        if (Imagick::installed()) {
            $alpha = $a / 255;
            $imagick = new \Imagick();
            $imagick->newImage(1, 1, new \ImagickPixel("rgba($r, $g, $b, $alpha)"));
            $imagick->setImageFormat('png');
            $contents = $imagick->getImageBlob();
            $imagick->clear();
            $imagick->destroy();
        } else {
            $image = imagecreatetruecolor(1, 1);
            imagefill($image, 0, 0, imagecolorallocate($image, $r, $g, $b));
            ob_start();
            imagepng($image);
            $contents = ob_get_contents();
            ob_end_clean();
            imagedestroy($image);
        }

        $data = base64_encode($contents);

        return "data:image/png;base64,{$data}";
    }

    protected function rgbaToHex(array $rgba): string
    {
        [$r, $g, $b, $a] = $rgba;

        return sprintf('#%02X%02X%02X%02X', $r, $g, $b, $a);
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
