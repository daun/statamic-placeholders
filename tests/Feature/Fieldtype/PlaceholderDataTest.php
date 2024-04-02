<?php

use Daun\StatamicPlaceholders\Support\PlaceholderData;
use Statamic\Facades\Stache;

beforeEach(function () {
    $this->addPlaceholderFieldToAssetBlueprint();
    $this->asset = $this->uploadTestFileToTestContainer('test.jpg');
    Stache::clear();
});

test('loads data', function () {
    $meta = $this->asset->get('placeholder', []);
    expect(PlaceholderData::load($this->asset))->toBeArray()->toEqual($meta);
});

test('saves and clears data', function () {
    PlaceholderData::save($this->asset, ['test' => 'data']);
    expect($this->asset->get('placeholder', []))->toEqual(['test' => 'data']);
    PlaceholderData::clear($this->asset);
    expect($this->asset->get('placeholder', []))->toEqual([]);
});

test('reads and updates hashes', function () {
    PlaceholderData::save($this->asset, ['test' => 'data']);
    expect(PlaceholderData::getHash($this->asset, 'test'))->toBe('data');

    PlaceholderData::addHash($this->asset, 'updated', 'test');
    expect(PlaceholderData::getHash($this->asset, 'test'))->toBe('updated');
});
