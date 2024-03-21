<?php

use Daun\StatamicPlaceholders\Providers\BlurHash;

test('creates a blurhash', function () {
    $provider = $this->app->make(BlurHash::class);

    $content = $this->getTestFileContents('test.jpg');
    $hash = $provider->encode($content);

    expect($hash)->toBeString()->not->toBeEmpty();
    expect($hash)->toMatchTextSnapshot();
});

test('creates a data uri', function () {
    $provider = $this->app->make(BlurHash::class);

    $content = $this->getTestFileContents('test.jpg');
    $hash = $provider->encode($content);
    $uri = $provider->decode($hash);

    expect($uri)->toBeString()->not->toBeEmpty();
    expect($uri)->toMatchTextSnapshot();
});

test('generates a thumb and extracts pixels', function () {
    $provider = Mockery::mock(BlurHash::class)->makePartial()->shouldAllowMockingProtectedMethods();

    $content = file_get_contents(fixtures_path('testfiles/test.jpg'));

    $provider->shouldReceive('thumb')->once()->withArgs([$content])->andReturn($content);
    $provider->shouldReceive('extractPixels')->withArgs([$content])->once()->andReturn([[[0, 0, 0]]]);

    $provider->encode($content);
});
