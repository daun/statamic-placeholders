<?php

use Daun\StatamicPlaceholders\Data\AssetPlaceholder;
use Daun\StatamicPlaceholders\Fieldtypes\PlaceholderFieldtype;
use Statamic\Assets\Asset;
use Statamic\Facades\Antlers;
use Statamic\Fields\Field;
use Tests\Fixtures\TestProvider;

beforeEach(function () {
    config(['placeholders.providers' => [TestProvider::class]]);
    $this->addPlaceholderFieldToAssetBlueprint(['placeholder_type' => 'test']);

    $this->asset = $this->uploadTestImageToTestContainer('test.jpg');
    $this->field = $this->asset->blueprint()->fields()->all()->first(
        fn (Field $field) => $field->type() === PlaceholderFieldtype::handle()
    );
    $this->fieldtype = $this->field->fieldtype();
});

test('exists', function () {
    expect($this->fieldtype)->toBeInstanceOf(PlaceholderFieldtype::class);
});

test('is called placeholder', function () {
    expect($this->fieldtype->handle())->toBe('placeholder');
});

test('allows setting placeholder', function () {
    $config = $this->fieldtype->configFields();
    $typeConfigField = $config->get('placeholder_type')->config();
    expect($typeConfigField['options'])->toContain('ThumbHash', 'BlurHash');
});

test('returns its asset', function () {
    expect($this->fieldtype->asset())->toBeInstanceOf(Asset::class);
});

test('preloads placeholder data', function () {
    $preload = $this->fieldtype->preload();
    expect($preload)->toHaveKey('provider.handle', 'test');
    expect($preload)->toHaveKey('is_asset', true);
    expect($preload)->toHaveKey('is_supported', true);
});

test('augments to placeholder object', function () {
    $augmented = $this->fieldtype->augment([]);
    expect($augmented)->toBeInstanceOf(AssetPlaceholder::class);
});

test('stringifies to uri', function () {
    $augmented = $this->fieldtype->augment([]);
    expect((string) $augmented)->toBe('test-uri');
});

test('placeholder object augments to array', function () {
    $augmented = $this->fieldtype->augment([]);
    $data = $augmented->toArray();
    expect($data)->toHaveKeys(['uri', 'hash', 'type', 'exists']);
    expect($data)->toMatchYamlSnapshot();
});

test('allows tag access to subproperties', function () {
    $data = ['placeholder_field' => $this->fieldtype->augment([])];
    $output = (string) $this->antlers('{{ placeholder_field }}{{ hash }} {{ uri }}{{ /placeholder_field }}', $data);
    expect($output)->toBeString()->toBe('test-hash test-uri');
});

test('allows direct access to subkeys', function () {
    $data = ['placeholder_field' => $this->fieldtype->augment([])];
    $output = (string) $this->antlers('{{ placeholder_field:hash }} {{ placeholder_field:uri }}', $data);
    expect($output)->toBeString()->toBe('test-hash test-uri');
});
