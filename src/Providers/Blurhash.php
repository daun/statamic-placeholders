<?php

namespace Daun\StatamicPlaceholders\Providers;

use Daun\StatamicPlaceholders\Contracts\PlaceholderProvider;
use Daun\StatamicPlaceholders\Support\Dimensions;
use kornrunner\Blurhash\Blurhash as BlurhashService;

class Blurhash extends PlaceholderProvider
{
    public static string $handle = 'blurhash';

    public static string $name = 'BlurHash';

    protected int $compX = 4;

    protected int $compY = 3;

    protected int $maxInputSize = 200;

    protected int $calcSize = 200;

    public function encode(string $contents): ?string
    {
        try {
            $pixels = $this->generatePixelMatrixFromImage($contents);
        } catch (\Throwable $th) {
        }
        if (! count($pixels ?? [])) {
            return null;
        }

        try {
            return BlurhashService::encode($pixels, $this->compX, $this->compY);
        } catch (\Exception $e) {
            throw new \Exception("Error encoding blurhash: {$e->getMessage()}");
        }
    }

    public function decode(string $hash, int $width = 0, int $height = 0): ?string
    {
        if (! $hash) {
            return null;
        }

        $width = $width ?: $this->calcSize;
        $height = $height ?: $this->calcSize;

        try {
            [$calcWidth, $calcHeight] = Dimensions::contain($width, $height, $this->calcSize);
            $pixels = BlurhashService::decode($hash, $calcWidth, $calcHeight);
        } catch (\Exception $e) {
            throw new \Exception("Error decoding blurhash: {$e->getMessage()}");
        }

        $image = $this->generateImageFromPixelMatrix($pixels, $width, $height);
        $data = base64_encode($image);

        return "data:image/png;base64,{$data}";
    }

    protected function generatePixelMatrixFromImage(?string $contents): array
    {
        if (! $contents) {
            return [];
        }

        $image = imagecreatefromstring($contents);
        [$width, $height] = Dimensions::contain(imagesx($image), imagesy($image), $this->maxInputSize);
        $image = imagescale($image, $width, $height);

        $pixels = [];
        for ($y = 0; $y < $height; $y++) {
            $row = [];
            for ($x = 0; $x < $width; $x++) {
                $index = imagecolorat($image, $x, $y);
                $colors = imagecolorsforindex($image, $index);
                $r = max(0, min(255, $colors['red']));
                $g = max(0, min(255, $colors['green']));
                $b = max(0, min(255, $colors['blue']));
                $row[] = [$r, $g, $b];
            }
            $pixels[] = $row;
        }

        return $pixels;
    }

    protected function generateImageFromPixelMatrix(array $pixels, int $width, int $height): string
    {
        if (! $pixels || ! count($pixels)) {
            return '';
        }

        [$calcWidth, $calcHeight] = Dimensions::contain($width, $height, $this->calcSize);
        $image = imagecreatetruecolor($calcWidth, $calcHeight);
        for ($y = 0; $y < $calcHeight; $y++) {
            for ($x = 0; $x < $calcWidth; $x++) {
                [$r, $g, $b] = $pixels[$y][$x];
                $r = max(0, min(255, $r));
                $g = max(0, min(255, $g));
                $b = max(0, min(255, $b));
                $allocate = imagecolorallocate($image, $r, $g, $b);
                imagesetpixel($image, $x, $y, $allocate);
            }
        }

        $image = imagescale($image, $width, -1);

        ob_start();
        imagepng($image);
        $contents = ob_get_contents();
        ob_end_clean();
        imagedestroy($image);

        return $contents;
    }
}
