<?php

namespace Daun\StatamicPlaceholders\Contracts;

interface PlaceholderProvider
{
    public static string $handle;

    public static string $name;

    /**
     * Generate a placeholder string from the contents of an image file.
     *
     * @param  string  $contents  The contents of the image file
     * @return ?string The generated placeholder string
     */
    public function encode(string $contents): ?string;

    /**
     * Generate a data URI from a placeholder string
     *
     * @param  string  $placeholder  The placeholder string to generate a data URI for
     * @return ?string The generated data URI
     */
    public function decode(string $placeholder, int $width = 0, int $height = 0): ?string;
}
