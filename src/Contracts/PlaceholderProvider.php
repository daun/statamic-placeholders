<?php

namespace Daun\StatamicPlaceholders\Contracts;

use Daun\StatamicPlaceholders\Services\ImageManager;

abstract class PlaceholderProvider
{
    public static string $handle;

    public static string $name;

    protected int $maxThumbSize = 100;

    public function __construct(protected ImageManager $manager)
    {
    }

    /**
     * Generate a placeholder string from the contents of an image file.
     *
     * @param  string  $contents  The contents of the image file
     * @return ?string The generated placeholder string
     */
    abstract public function encode(string $contents): ?string;

    /**
     * Generate a data URI from a placeholder string
     *
     * @param  string  $placeholder  The placeholder string to generate a data URI for
     * @return ?string The generated data URI
     */
    abstract public function decode(string $placeholder, int $width = 0, int $height = 0): ?string;

    /**
     * Scale down the image to thumbnail size for faster processing.
     *
     * @param  string  $contents  The contents of the image file
     * @return string The contents of the thumbnail
     */
    protected function thumb(string $contents): string
    {
        $thumb = $this->manager->make($contents);
        $thumb =  $this->manager->fit($thumb, $this->maxThumbSize)->encode();

        $result = (string) $thumb;
        $thumb->destroy();

        return $result;
    }
}
