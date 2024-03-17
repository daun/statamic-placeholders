<?php

namespace Daun\StatamicPlaceholders\Providers;

use Daun\StatamicPlaceholders\Contracts\PlaceholderProvider;

class None implements PlaceholderProvider
{
    public static string $name = '';

    public function encode(string $contents): ?string
    {
        return null;
    }

    public function decode(string $placeholder, int $width = 0, int $height = 0): ?string
    {
        return null;
    }
}
