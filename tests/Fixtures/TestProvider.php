<?php

namespace Tests\Fixtures;

use Daun\StatamicPlaceholders\Contracts\PlaceholderProvider;

class TestProvider extends PlaceholderProvider
{
    public static string $handle = 'test';

    public static string $name = 'Test';

    public function encode(string $contents): ?string
    {
        return 'test-hash';
    }

    public function decode(string $placeholder, int $width = 0, int $height = 0): ?string
    {
        return 'test-uri';
    }
}
