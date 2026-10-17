<?php

use Daun\StatamicPlaceholders\Providers\ThumbHash;
use Daun\StatamicPlaceholders\Services\ImageService;
use Intervention\Image\Interfaces\DriverInterface;

use function Thumbhash\extract_size_and_pixels_with_gd;

test('creates a thumbhash', function () {
    $provider = $this->app->make(ThumbHash::class);

    $content = $this->getTestFileContents('test.jpg');
    $hash = $provider->encode($content);

    expect($hash)->toBeString()->not->toBeEmpty();
    expect($hash)->toMatchTextSnapshot();
});

test('creates a data uri', function () {
    $provider = $this->app->make(ThumbHash::class);

    $content = $this->getTestFileContents('test.jpg');
    $hash = $provider->encode($content);
    $uri = $provider->decode($hash);

    expect($uri)->toBeString()->not->toBeEmpty();
    expect($uri)->toMatchTextSnapshot();
});

test('generates a thumb and extracts pixels', function () {
    $provider = Mockery::mock(ThumbHash::class)->makePartial()->shouldAllowMockingProtectedMethods();

    $content = file_get_contents(fixtures_path('testfiles/test.jpg'));

    $provider->shouldReceive('thumb')->once()->withArgs([$content])->andReturn($content);
    $provider->shouldReceive('extractSizeAndPixels')->once()->andReturn([1, 1, [0, 0, 0, 0]]);

    $provider->encode($content);
});

test('falls back to intervention for drivers without native helpers', function () {
    $service = Mockery::mock(ImageService::class.'[driverInstance]', [$this->app['config']]);
    $service->shouldReceive('driverInstance')->andReturn(Mockery::mock(DriverInterface::class));

    $provider = new ThumbHash($service);
    $content = $this->getTestFileContents('test.jpg');

    expect($provider->encode($content))->toBe($this->app->make(ThumbHash::class)->encode($content));
});

test('extracts the same pixels with intervention as with gd', function () {
    $provider = new class($this->app->make(ImageService::class)) extends ThumbHash
    {
        public function pixels(string $contents): array
        {
            return [
                extract_size_and_pixels_with_gd($contents),
                $this->extractSizeAndPixelsWithIntervention($contents),
            ];
        }
    };

    $thumb = (fn ($c) => $this->thumb($c))->call($provider, $this->getTestFileContents('test.jpg'));
    [$gd, $intervention] = $provider->pixels($thumb);

    expect($intervention)->toEqual($gd);
});
