<?php

use Daun\StatamicPlaceholders\Providers\BlurHash;

test('creates a blurhash', function () {
    $provider = $this->app->make(BlurHash::class);

    [$content, $expected] = $this->getTestFileData('test.jpg');
    ['hash' => $hash, 'uri' => $uri] = $expected[$provider::$handle];

    expect($hash)->toBeString()->not->toBeEmpty();
    expect($provider->encode($content))->toBe($hash);
});

test('creates a data uri', function () {
    $provider = $this->app->make(BlurHash::class);

    [$content, $expected] = $this->getTestFileData('test.jpg');
    ['hash' => $hash, 'uri' => $uri] = $expected[$provider::$handle];

    expect($hash)->toBeString()->not->toBeEmpty();
    expect($uri)->toBeString()->not->toBeEmpty();
    expect($provider->decode($hash))->toEqual($uri);
});

test('generates a thumb and extracts pixels', function () {
    $provider = Mockery::mock(BlurHash::class)->makePartial()->shouldAllowMockingProtectedMethods();

    $content = file_get_contents(fixtures_path('testfiles/test.jpg'));

    $provider->shouldReceive('thumb')->once()->withArgs([$content])->andReturn($content);
    $provider->shouldReceive('extractPixels')->withArgs([$content])->once()->andReturn([[[0, 0, 0]]]);

    $provider->encode($content);
});
