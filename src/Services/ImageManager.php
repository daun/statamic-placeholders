<?php

namespace Daun\StatamicPlaceholders\Services;

use Illuminate\Contracts\Config\Repository;
use Intervention\Image\Image;
use Intervention\Image\ImageManager as InterventionImageManager;

class ImageManager extends InterventionImageManager
{
    const DRIVER_GD = 'gd';

    const DRIVER_IMAGICK = 'imagick';

    public function __construct(public Repository $configRepository)
    {
        parent::__construct(['driver' => $this->driver()]);
    }

    public function driver(): string
    {
        return $this->configRepository->get('statamic.assets.image_manipulation.driver');
    }

    /**
     * Resize an Image instance proportionally to a maximum size.
     */
    public function fit(mixed $image, int $size): Image
    {
        return $this->make($image)->resize($size, $size, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
    }
}
