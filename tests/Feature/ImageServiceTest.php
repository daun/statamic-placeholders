<?php

use Daun\StatamicPlaceholders\Services\ImageService;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Interfaces\ImageInterface;

use function Thumbhash\extract_size_and_pixels_with_gd;

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

function intervention_pixels(ImageService $service, ImageInterface $image): array
{
    return (fn () => $this->pixelsFromIntervention($image))->call($service);
}

function thumbnail(ImageService $service, string $contents): ImageInterface
{
    return $service->make($contents)->scaleDown(100, 100);
}

test('reads pixels as flat list of rgba values', function () {
    $image = $this->service->create(2, 1)->fill('#ff0000')->drawPixel(1, 0, '#0000ff');

    expect($this->service->pixels($image))->toEqual([255, 0, 0, 255, 0, 0, 255, 255]);
});

// Only opaque images: GD stores 7-bit alpha, and Intervention rounds it to 8-bit
// differently than the thumbhash library, e.g. 129 instead of 128
test('reads the same pixels natively as via intervention', function (string $file) {
    $thumb = thumbnail($this->service, $this->getTestFileContents($file));

    expect($this->service->pixels($thumb))->toEqual(intervention_pixels($this->service, $thumb));
})->with(['test.jpg', 'test.gif']);

// Including alpha: the 7-bit GD alpha must be converted exactly like the thumbhash library does
test('reads the same pixels natively as the thumbhash library', function (string $file) {
    $thumb = thumbnail($this->service, $this->getTestFileContents($file));
    [, , $pixels] = extract_size_and_pixels_with_gd(Daun\StatamicPlaceholders\Support\Intervention::encodeByExtension($thumb, 'png')->toString());

    expect($this->service->pixels($thumb))->toEqual($pixels);
})->with(['test.jpg', 'test.png', 'test.gif']);

test('draws the same pixels natively as via intervention', function () {
    $pixels = [255, 0, 0, 0, 255, 0, 0, 0, 255, 10, 20, 30, 40, 50, 60, 70, 80, 90];

    $native = $this->service->drawPixels($this->service->create(3, 2), $pixels);
    $fallback = $this->service->create(3, 2);
    (fn () => $this->drawPixelsWithIntervention($fallback, $pixels))->call($this->service);

    expect($this->service->pixels($native))->toEqual($this->service->pixels($fallback));
    expect($this->service->pixels($native))->toEqual([
        255, 0, 0, 255, 0, 255, 0, 255, 0, 0, 255, 255,
        10, 20, 30, 255, 40, 50, 60, 255, 70, 80, 90, 255,
    ]);
});

test('draws and reads pixels with imagick', function () {
    $this->app['config']->set('statamic.assets.image_manipulation.driver', 'imagick');
    $service = new ImageService($this->app['config']);
    $pixels = [255, 0, 0, 0, 255, 0, 0, 0, 255, 10, 20, 30, 40, 50, 60, 70, 80, 90];

    $image = $service->drawPixels($service->create(3, 2), $pixels);

    expect($image->core()->native())->toBeInstanceOf(Imagick::class);
    expect($service->pixels($image))->toEqual(intervention_pixels($service, $image));
    expect($service->pixels($image))->toEqual([
        255, 0, 0, 255, 0, 255, 0, 255, 0, 0, 255, 255,
        10, 20, 30, 255, 40, 50, 60, 255, 70, 80, 90, 255,
    ]);
})->skip(! extension_loaded('imagick'), 'Imagick not installed');
