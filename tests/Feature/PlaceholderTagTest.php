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

test('renders uri', function() {
    $output = $this->tag->uri();

    expect($output)->toBeString()->toContain('data:image/');
    $this->assertMatchesSnapshot($output);
});

test('renders hash', function() {
    $output = $this->tag->hash();

    expect($output)->toBeString();
    $this->assertMatchesSnapshot($output);
});

test('renders img', function() {
    $output = $this->tag->img();

    expect($output)->toBeString()->toContain('<img src="data:image/');
    $this->assertMatchesSnapshot($output);
});

test('renders img attributes', function() {
    $output = $this->tag->setParameters(['data-lazyload' => 'yes'])->img();

    expect($output)->toBeString()->toContain('<img src="data:image/')->toContain('data-lazyload="yes"');
    $this->assertMatchesSnapshot($output);
});
