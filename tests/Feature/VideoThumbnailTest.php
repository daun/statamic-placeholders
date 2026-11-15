<?php

use Daun\StatamicPlaceholders\Facades\Placeholders;
use Daun\StatamicPlaceholders\Models\AssetPlaceholder;
use Daun\StatamicPlaceholders\Support\VideoThumbnail;

beforeEach(function () {
    if (! VideoThumbnail::available()) {
        $this->markTestSkipped('FFmpeg is not available');
    }
});

test('extracts a thumbnail from videos', function () {
    $asset = $this->uploadTestFileToTestContainer('test.mp4');

    $path = VideoThumbnail::path($asset);

    expect($path)->toStartWith($this->getTempDirectory('ffmpeg'));
    expect(getimagesize($path))->toMatchArray([0 => 160, 1 => 90, 'mime' => 'image/jpeg']);
});

test('returns no thumbnail for images', function () {
    $asset = $this->uploadTestFileToTestContainer('test.jpg');

    expect(VideoThumbnail::path($asset))->toBeNull();
    expect(VideoThumbnail::contents($asset))->toBeNull();
});

test('returns no thumbnail if video thumbnails are disabled', function () {
    config(['statamic.assets.video_thumbnails' => false]);

    $asset = $this->uploadTestFileToTestContainer('test.mp4');

    expect(VideoThumbnail::path($asset))->toBeNull();
});

test('generates placeholders for videos', function (string $provider) {
    $this->addPlaceholderFieldToAssetBlueprint();
    $asset = $this->uploadTestFileToTestContainer('test.mp4');

    expect(Placeholders::make($asset))->toBeInstanceOf(AssetPlaceholder::class);
    expect(Placeholders::hash($asset, $provider))->toBeString()->not->toBeEmpty();
    expect(Placeholders::uri($asset, $provider))->toStartWith('data:image/');
})->with(['thumbhash', 'blurhash', 'average']);
