<?php

use Daun\StatamicPlaceholders\Providers\ThumbHash;

test('creates a thumbhash', function () {
    $provider = $this->app->make(ThumbHash::class);

    [$content, $expected] = $this->getTestFileData('test.jpg');
    ['hash' => $hash, 'uri' => $uri] = $expected[$provider::$handle];

    expect($hash)->toBeString()->not->toBeEmpty();
    expect($provider->encode($content))->toBe($hash);
});

test('creates a data uri', function () {
    $provider = $this->app->make(ThumbHash::class);

    [$content, $expected] = $this->getTestFileData('test.jpg');
    ['hash' => $hash, 'uri' => $uri] = $expected[$provider::$handle];

    expect($hash)->toBeString()->not->toBeEmpty();
    expect($uri)->toBeString()->not->toBeEmpty();
    expect($provider->decode($hash))->toEqual($uri);
});

test('generates a thumb and extracts pixels', function () {
    $provider = Mockery::mock(ThumbHash::class)->makePartial()->shouldAllowMockingProtectedMethods();

    $content = file_get_contents(fixtures_path('testfiles/test.jpg'));

    $provider->shouldReceive('thumb')->once()->withArgs([$content])->andReturn($content);
    $provider->shouldReceive('extractSizeAndPixels')->once()->andReturn([1, 1, [0, 0, 0, 0]]);

    $provider->encode($content);
});
