<?php

namespace Daun\StatamicPlaceholders\Providers;

use Daun\StatamicPlaceholders\Contracts\PlaceholderProvider;

class None extends PlaceholderProvider
{
    public static string $handle = '';

    public static string $name = 'None';

    public function encode(string $contents): ?string
    {
        return null;
    }

    public function decode(string $placeholder, int $width = 0, int $height = 0): ?string
    {
        return null;
    }
}
