<?php

use Daun\StatamicPlaceholders\Fieldtypes\PlaceholderFieldtype;
use Daun\StatamicPlaceholders\Support\PlaceholderField;
use Daun\StatamicPlaceholders\Support\VideoThumbnail;
use Statamic\Fields\Field;

test('checks asset support', function () {
    $jpg = $this->makeEmptyAsset('test.jpg');
    $gif = $this->makeEmptyAsset('test.gif');
    $svg = $this->makeEmptyAsset('test.svg');
    $mp4 = $this->makeEmptyAsset('test.mp4');
    $pdf = $this->makeEmptyAsset('test.pdf');

    expect(PlaceholderField::supportsAssetType($jpg))->toBeTrue();
    expect(PlaceholderField::supportsAssetType($gif))->toBeTrue();
    expect(PlaceholderField::supportsAssetType($svg))->toBeFalse();
    expect(PlaceholderField::supportsAssetType($mp4))->toBe(VideoThumbnail::available());
    expect(PlaceholderField::supportsAssetType($pdf))->toBeFalse();
});

test('does not support videos if video thumbnails are disabled', function () {
    config(['statamic.assets.video_thumbnails' => false]);

    expect(PlaceholderField::supportsAssetType($this->makeEmptyAsset('test.mp4')))->toBeFalse();
});

test('checks blueprint for fieldtype', function () {
    $asset = $this->uploadTestFileToTestContainer('test.jpg');
    expect(PlaceholderField::existsInBlueprint($asset))->toBeFalse();
    expect(fn () => PlaceholderField::assertExistsInBlueprint($asset))->toThrow(Exception::class);

    $this->addPlaceholderFieldToAssetBlueprint();
    $asset = $this->uploadTestFileToTestContainer('test.jpg');
    expect(PlaceholderField::existsInBlueprint($asset))->toBeTrue();
});

test('gets field object from blueprint', function () {
    $this->addPlaceholderFieldToAssetBlueprint();
    $asset = $this->uploadTestFileToTestContainer('test.jpg');
    $field = PlaceholderField::getFromBlueprint($asset);

    expect($field)->toBeInstanceOf(Field::class);
    expect($field->fieldtype())->toBeInstanceOf(PlaceholderFieldtype::class);
});

test('gets configured provider from blueprint', function () {
    $this->addPlaceholderFieldToAssetBlueprint();
    $asset = $this->uploadTestFileToTestContainer('test.jpg');
    expect(PlaceholderField::getProvider($asset))->toBe(null);

    $this->addPlaceholderFieldToAssetBlueprint(['placeholder_type' => 'blurhash']);
    $asset = $this->uploadTestFileToTestContainer('test.jpg');
    expect(PlaceholderField::getProvider($asset))->toBe('blurhash');
});
