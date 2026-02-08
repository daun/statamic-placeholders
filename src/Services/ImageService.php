<?php

namespace Daun\StatamicPlaceholders\Services;

use Illuminate\Contracts\Config\Repository;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;

class ImageService
{
    const DRIVER_GD = 'gd';

    const DRIVER_IMAGICK = 'imagick';

    protected ImageManager $manager;

    public function __construct(public Repository $config)
    {
        $this->manager = new ImageManager(
            $this->driverClass(),
            decodeAnimation: false,
            strip: true
        );
    }

    public function driver(): string
    {
        return $this->config->get('statamic.assets.image_manipulation.driver');
    }

    public function driverClass(): string
    {
        return match ($driver = $this->driver()) {
            self::DRIVER_GD => \Intervention\Image\Drivers\Gd\Driver::class,
            self::DRIVER_IMAGICK => \Intervention\Image\Drivers\Imagick\Driver::class,
            default => throw new \Exception("Unsupported driver: {$driver}"),
        };
    }

    public function manager(): ImageManager
    {
        return $this->manager;
    }

    public function make(mixed $image): ImageInterface
    {
        return $this->manager->read($image);
    }

    public function supports(mixed $image): bool
    {
        return $this->manager->driver()->supports($image);
    }
}
