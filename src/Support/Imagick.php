<?php

namespace Daun\StatamicPlaceholders\Support;

class Imagick
{
    public static function installed(): bool
    {
        return class_exists('\\Imagick');
    }
}
