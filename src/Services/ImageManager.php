<?php

namespace Daun\StatamicPlaceholders\Services;

use Intervention\Image\ImageManager as InterventionImageManager;
use Illuminate\Contracts\Config\Repository;

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
}
