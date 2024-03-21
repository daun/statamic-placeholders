<?php

use Daun\StatamicPlaceholders\Tags\PlaceholderTag;
use Statamic\Facades\Stache;

beforeEach(function () {
    $this->addPlaceholderFieldToAssetBlueprint();

    $this->assetJpg = $this->uploadTestImageToTestContainer('test.jpg');
    $this->assetJpg2 = $this->uploadTestImageToTestContainer('test.jpg', 'test2.jpg');
    $this->assetSvg = $this->uploadTestImageToTestContainer('test.svg');
    $this->assetGif = $this->uploadTestImageToTestContainer('test.gif');

    $this->tag = $this->app->make(PlaceholderTag::class)
        ->setContext(['asset' => $this->assetJpg])
        ->setParameters([]);

    Stache::clear();
});

test('renders uri', function () {
    $uri = $this->tag->uri();

    expect($uri)->toBeString()->toContain('data:image/');
    $this->assertMatchesSnapshot($uri);

    $index = $this->tag->index();
    expect($index)->toBe($uri);
});

test('renders hash', function () {
    $hash = $this->tag->hash();

    expect($hash)->toBeString();
    $this->assertMatchesSnapshot($hash);
});

test('renders img', function () {
    $img = $this->tag->img();

    expect($img)->toBeString()->toContain('<img src="data:image/');
    $this->assertMatchesSnapshot($img);
});

test('renders img attributes', function () {
    $img = $this->tag->setParameters(['data-lazyload' => 'yes'])->img();

    expect($img)->toBeString()->toContain('<img src="data:image/')->toContain('data-lazyload="yes"');
    $this->assertMatchesSnapshot($img);
});

test('returns available data', function () {
    $data = $this->tag->data();

    expect($data)->toBeArray()->toHaveKeys(['uri', 'hash', 'type', 'exists']);
    $this->assertMatchesObjectSnapshot($data);
});
