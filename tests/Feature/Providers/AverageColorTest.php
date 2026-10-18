<?php

use Daun\StatamicPlaceholders\Providers\AverageColor;
use Daun\StatamicPlaceholders\Services\ImageService;

test('extracts the average color', function () {
    $provider = $this->app->make(AverageColor::class);

    $content = $this->getTestFileContents('test.jpg');
    $hash = $provider->encode($content);

    expect($hash)->toBeString()->not->toBeEmpty();
    expect($hash)->toMatchTextSnapshot();
});

test('extracts the average color with alpha', function () {
    $provider = $this->app->make(AverageColor::class);

    $content = $this->getTestFileContents('test.png');
    $hash = $provider->encode($content);

    expect($hash)->toBeString()->not->toBeEmpty();
    expect($hash)->toMatchTextSnapshot();
})->with([intervention_version()]);

test('creates a data uri', function () {
    $provider = $this->app->make(AverageColor::class);

    $content = $this->getTestFileContents('test.jpg');
    $hash = $provider->encode($content);
    $uri = $provider->decode($hash);

    expect($uri)->toBeString()->not->toBeEmpty();
    expect($uri)->toMatchTextSnapshot();
})->with([intervention_version()]);

test('generates a thumbnail and calculates average', function () {
    $provider = Mockery::mock(AverageColor::class)->makePartial()->shouldAllowMockingProtectedMethods();

    $content = file_get_contents(fixtures_path('testfiles/test.jpg'));
    $thumb = $this->app->make(ImageService::class)->create(1, 1);

    $provider->shouldReceive('thumbnail')->once()->withArgs([$content])->andReturn($thumb);
    $provider->shouldReceive('calculateAverage')->once()->withArgs([$thumb])->andReturn('#00000000');

    $provider->encode($content);
});
