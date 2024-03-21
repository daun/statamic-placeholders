<?php

use Daun\StatamicPlaceholders\Providers\ThumbHash;

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
