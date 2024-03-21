<?php

use Daun\StatamicPlaceholders\Providers\Thumbhash;

test('creates a Thumbhash', function () {
    $provider = $this->app->make(Thumbhash::class);

    [$content, $expected] = $this->getTestFileData('test.jpg');
    ['hash' => $hash, 'uri' => $uri] = $expected[$provider::$handle];

    expect($hash)->toBeString()->not->toBeEmpty();
    expect($provider->encode($content))->toBe($hash);
});

test('creates a data uri', function () {
    $provider = $this->app->make(Thumbhash::class);

    [$content, $expected] = $this->getTestFileData('test.jpg');
    ['hash' => $hash, 'uri' => $uri] = $expected[$provider::$handle];

    expect($hash)->toBeString()->not->toBeEmpty();
    expect($uri)->toBeString()->not->toBeEmpty();
    expect($provider->decode($hash))->toEqual($uri);
});

test('generates a thumb and pixel matrix', function () {
    $provider = Mockery::mock(Thumbhash::class)->makePartial()->shouldAllowMockingProtectedMethods();

    $content = file_get_contents(fixtures_path('testfiles/test.jpg'));

    $provider->shouldReceive('thumb')->once()->withArgs([$content])->andReturn($content);
    $provider->shouldReceive('extractSizeAndPixels')->once()->andReturn([1, 1, [0, 0, 0, 0]]);

    $provider->encode($content);
});
