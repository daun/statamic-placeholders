<?php

use Daun\StatamicPlaceholders\Services\ImageService;
use Intervention\Image\Drivers\Gd\Driver;

beforeEach(function () {
    $this->service = $this->app->make(ImageService::class);
});

test('reports correct driver', function () {
    $this->app['config']->set('statamic.assets.image_manipulation.driver', 'test');
    expect($this->service->driver())->toBe('test');
});

test('resolves driver shorthands to intervention drivers', function (string $driver, string $class) {
    $this->app['config']->set('statamic.assets.image_manipulation.driver', $driver);
    expect($this->service->driverClass())->toBe($class);
})->with([
    'gd' => ['gd', Driver::class],
    'imagick' => ['imagick', Intervention\Image\Drivers\Imagick\Driver::class],
]);

test('passes through custom driver classes', function () {
    $this->app['config']->set('statamic.assets.image_manipulation.driver', 'Intervention\Image\Drivers\Vips\Driver');
    expect($this->service->driverClass())->toBe('Intervention\Image\Drivers\Vips\Driver');
});

test('reads driver from glide driver options array', function () {
    $this->app['config']->set('statamic.assets.image_manipulation.driver', ['driver' => 'imagick', 'quality' => 80]);
    expect($this->service->driver())->toBe('imagick');
    expect($this->service->driverClass())->toBe(Intervention\Image\Drivers\Imagick\Driver::class);
});

test('creates image manager with custom driver class', function () {
    $this->app['config']->set('statamic.assets.image_manipulation.driver', Driver::class);
    $service = new ImageService($this->app['config']);
    expect($service->driverInstance())->toBeInstanceOf(Driver::class);
});
