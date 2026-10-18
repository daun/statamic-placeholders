<?php

use Daun\StatamicPlaceholders\Providers\BlurHash;
use Daun\StatamicPlaceholders\Services\ImageService;

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
})->with([intervention_version()]);

test('generates a thumbnail and extracts pixels', function () {
    $provider = Mockery::mock(BlurHash::class)->makePartial()->shouldAllowMockingProtectedMethods();

    $content = file_get_contents(fixtures_path('testfiles/test.jpg'));
    $thumb = $this->app->make(ImageService::class)->create(1, 1);

    $provider->shouldReceive('thumbnail')->once()->withArgs([$content])->andReturn($thumb);
    $provider->shouldReceive('extractPixels')->withArgs([$thumb])->once()->andReturn([[[0, 0, 0]]]);

    $provider->encode($content);
});
