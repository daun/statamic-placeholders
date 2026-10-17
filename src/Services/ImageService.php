<?php

namespace Daun\StatamicPlaceholders\Services;

use Daun\StatamicPlaceholders\Support\Intervention;
use Illuminate\Contracts\Config\Repository;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\DriverInterface;
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

    /**
     * The image driver configured for Statamic: "gd", "imagick" or the class name of
     * any other Intervention driver, e.g. the libvips driver.
     */
    public function driver(): string
    {
        $driver = $this->config->get('statamic.assets.image_manipulation.driver') ?? self::DRIVER_GD;

        // Glide also accepts an array of driver options with the driver under the `driver` key
        return is_array($driver) ? ($driver['driver'] ?? self::DRIVER_GD) : $driver;
    }

    /**
     * Resolve the configured driver to an Intervention driver class, the same way Statamic and Glide do.
     */
    public function driverClass(): string
    {
        return match ($driver = $this->driver()) {
            self::DRIVER_GD => GdDriver::class,
            self::DRIVER_IMAGICK => ImagickDriver::class,
            default => $driver,
        };
    }

    public function driverInstance(): DriverInterface
    {
        return Intervention::driver($this->manager);
    }

    public function manager(): ImageManager
    {
        return $this->manager;
    }

    public function make(mixed $image): ImageInterface
    {
        return Intervention::decode($this->manager, $image);
    }

    public function create(int $width, int $height): ImageInterface
    {
        return Intervention::createImage($this->manager, $width, $height);
    }

    public function supports(mixed $image): bool
    {
        return $this->driverInstance()->supports($image);
    }
}
