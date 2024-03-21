<?php

namespace Daun\StatamicPlaceholders\Support;

class Color
{
    public static function rgbaToHex(array $rgba): string
    {
        return strtolower(vsprintf('#%02X%02X%02X%02X', $rgba));
    }

    public static function hexToRgba(string $hex): array
    {
        $hex = ltrim($hex, '#');
        $channels = array_map(
            fn ($c) => hexdec(str_pad($c, 2, $c)),
            str_split($hex, strlen($hex) > 3 ? 2 : 1)
        );

        return array_pad($channels, 4, 255);
    }
}
