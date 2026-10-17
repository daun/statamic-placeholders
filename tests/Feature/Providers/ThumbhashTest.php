<?php

use Daun\StatamicPlaceholders\Providers\ThumbHash;
use Daun\StatamicPlaceholders\Services\ImageService;

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

test('generates a thumbnail and reads its pixels', function () {
    $service = Mockery::mock(ImageService::class.'[pixels]', [$this->app['config']]);
    $provider = Mockery::mock(ThumbHash::class, [$service])->makePartial()->shouldAllowMockingProtectedMethods();

    $content = $this->getTestFileContents('test.jpg');
    $thumb = $service->create(1, 1);

    $provider->shouldReceive('thumbnail')->once()->withArgs([$content])->andReturn($thumb);
    $service->shouldReceive('pixels')->once()->withArgs([$thumb])->andReturn([0, 0, 0, 0]);

    $provider->encode($content);
});
