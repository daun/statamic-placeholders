<?php

use Daun\StatamicPlaceholders\Providers\AverageColor;

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
});

test('creates a data uri', function () {
    $provider = $this->app->make(AverageColor::class);

    $content = $this->getTestFileContents('test.jpg');
    $hash = $provider->encode($content);
    $uri = $provider->decode($hash);

    expect($uri)->toBeString()->not->toBeEmpty();
    expect($uri)->toMatchTextSnapshot();
});

test('generates a thumb and calculates average', function () {
    $provider = Mockery::mock(AverageColor::class)->makePartial()->shouldAllowMockingProtectedMethods();

    $content = file_get_contents(fixtures_path('testfiles/test.jpg'));

    $provider->shouldReceive('thumb')->once()->withArgs([$content])->andReturn($content);
    $provider->shouldReceive('calculateAverage')->once()->andReturn([0, 0, 0, 0]);

    $provider->encode($content);
});
