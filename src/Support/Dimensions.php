<?php

namespace Daun\StatamicPlaceholders\Support;

class Dimensions
{
    public static function contain(int $width, int $height, int $max): array
    {
        $width = $width ?: $max;
        $height = $height ?: $width ?: $max;

        $ratio = $width / $height;
        if ($width >= $height) {
            $width = min($width, $max);
            $height = floor($width / $ratio);
        } else {
            $height = min($height, $max);
            $width = floor($height * $ratio);
        }

        return [$width, $height];
    }
}
