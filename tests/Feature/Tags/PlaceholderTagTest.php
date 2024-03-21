<?php

use Daun\StatamicPlaceholders\Tags\PlaceholderTag;
use Statamic\Facades\Stache;

beforeEach(function () {
    $this->addPlaceholderFieldToAssetBlueprint();

    $this->jpg = $this->uploadTestImageToTestContainer('test.jpg');
    $this->png = $this->uploadTestImageToTestContainer('test.png');

    $this->tag = $this->app->make(PlaceholderTag::class)
        ->setContext(['asset' => $this->jpg])
        ->setParameters([]);

    Stache::clear();
});

test('renders uri', function () {
    $uri = $this->tag->uri();
    expect($uri)->toBeString()->toContain('data:image/');
    expect($uri)->toMatchTextSnapshot();

    $index = $this->tag->index();
    expect($index)->toBe($uri);
});

test('renders hash', function () {
    $hash = $this->tag->hash();
    expect($hash)->toBeString();
    expect($hash)->toMatchTextSnapshot();
});

test('renders img', function () {
    $img = $this->tag->img();

    expect($img)->toBeString()->toContain('<img src="data:image/');
    expect($img)->toMatchTextSnapshot();
});

test('renders img attributes', function () {
    $img = $this->tag->setParameters(['data-lazyload' => 'yes'])->img();

    expect($img)->toBeString()->toContain('<img src="data:image/')->toContain('data-lazyload="yes"');
    expect($img)->toMatchTextSnapshot();
});

test('returns available data', function () {
    $data = $this->tag->data();

    expect($data)->toBeArray()->toHaveKeys(['uri', 'hash', 'type', 'exists']);
    expect($data)->toMatchObjectSnapshot();
});

test('accepts asset param', function () {
    $uri = $this->tag->setParameters(['asset' => '/test/test.png'])->uri();

    expect($uri)->toBeString();
    expect($uri)->toMatchTextSnapshot();
});