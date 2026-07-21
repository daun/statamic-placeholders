<?php

namespace Daun\StatamicPlaceholders\Support;

use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ColorInterface;
use Intervention\Image\Interfaces\DriverInterface;
use Intervention\Image\Interfaces\EncodedImageInterface;
use Intervention\Image\Interfaces\ImageInterface;

/**
 * Smooths over the differences between Intervention Image v3 and v4.
 *
 * @internal
 */
class Intervention
{
    public static function isV4(): bool
    {
        // v4 dropped the driver-specific named constructors in favour of usingDriver().
        return ! method_exists(ImageManager::class, 'withDriver');
    }

    public static function decode(ImageManager $manager, mixed $input): ImageInterface
    {
        return static::isV4()
            ? $manager->decode($input)
            : $manager->read($input);
    }

    public static function createImage(ImageManager $manager, int $width, int $height): ImageInterface
    {
        return static::isV4()
            ? $manager->createImage($width, $height)
            : $manager->create($width, $height);
    }

    public static function driver(ImageManager $manager): DriverInterface
    {
        return static::isV4()
            ? $manager->driver
            : $manager->driver();
    }

    public static function colorAt(ImageInterface $image, int $x, int $y): ColorInterface
    {
        return static::isV4()
            ? $image->colorAt($x, $y)
            : $image->pickColor($x, $y);
    }

    public static function toColorspace(ColorInterface $color, string $colorspace): ColorInterface
    {
        return static::isV4()
            ? $color->toColorspace($colorspace)
            : $color->convertTo($colorspace);
    }

    /**
     * Channel values of a color, e.g. [r, g, b, a] for RGB colors.
     */
    public static function colorChannels(ColorInterface $color): array
    {
        return static::isV4()
            ? array_map(fn ($channel) => $channel->value(), $color->channels())
            : $color->toArray();
    }

    public static function toHex(ColorInterface $color, string $prefix = ''): string
    {
        // The prefix parameter changed from string (v3) to bool (v4),
        // so prepend it manually instead of passing it through.
        return $prefix.$color->toHex();
    }

    public static function encodeByExtension(ImageInterface $image, string $extension): EncodedImageInterface
    {
        return static::isV4()
            ? $image->encodeUsingFileExtension($extension)
            : $image->encodeByExtension($extension);
    }

    public static function toDataUri(EncodedImageInterface $encoded): string
    {
        // v4 returns a DataUri object instead of a string.
        $uri = $encoded->toDataUri();

        return is_string($uri) ? $uri : $uri->toString();
    }
}
