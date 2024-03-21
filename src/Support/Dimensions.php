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
            $width = $max;
            $height = floor($max / $ratio);
        } else {
            $width = floor($max * $ratio);
            $height = $max;
        }

        return [$width, $height];
    }
}
