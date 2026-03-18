<?php

use Daun\StatamicPlaceholders\Listeners\GeneratePlaceholder;
use Daun\StatamicPlaceholders\Services\PlaceholderService;
use Statamic\Events\AssetReuploaded;
use Statamic\Events\AssetUploaded;

beforeEach(function () {
    $this->service = Mockery::mock(PlaceholderService::class);
});

test('checks if service is enabled', function () {
    $asset = $this->makeEmptyAsset('file.jpg');

    $this->service->shouldReceive('enabled')->twice();

    (new GeneratePlaceholder($this->service))->handle(new AssetUploaded($asset, null));
    (new GeneratePlaceholder($this->service))->handle(new AssetReuploaded($asset, null));
});

test('does not generate placeholders by default', function () {
    $asset = $this->makeEmptyAsset('file.jpg');

    $this->service->shouldReceive('enabled')->twice();
    $this->service->shouldReceive('dispatch')->never();

    (new GeneratePlaceholder($this->service))->handle(new AssetUploaded($asset, null));
    (new GeneratePlaceholder($this->service))->handle(new AssetReuploaded($asset, null));
});

test('generates placeholders in enabled containers', function () {
    $this->addPlaceholderFieldToAssetBlueprint();

    $asset = $this->makeEmptyAsset('file.jpg');

    $this->service->shouldReceive('enabled')->twice()->andReturn(true);
    $this->service->shouldReceive('exists')->twice()->andReturn(false);
    $this->service->shouldReceive('dispatch')->twice()->with($asset);

    (new GeneratePlaceholder($this->service))->handle(new AssetUploaded($asset, null));
    (new GeneratePlaceholder($this->service))->handle(new AssetReuploaded($asset, null));
});

test('generates no placeholders for existing assets', function () {
    $this->addPlaceholderFieldToAssetBlueprint();

    $asset = $this->makeEmptyAsset('file.jpg');

    $this->service->shouldReceive('enabled')->twice()->andReturn(true);
    $this->service->shouldReceive('exists')->twice()->andReturn(true);
    $this->service->shouldReceive('dispatch')->never();

    (new GeneratePlaceholder($this->service))->handle(new AssetUploaded($asset, null));
    (new GeneratePlaceholder($this->service))->handle(new AssetReuploaded($asset, null));
});

test('generates no placeholders for unsupported assets', function () {
    $this->addPlaceholderFieldToAssetBlueprint();

    $txt = $this->makeEmptyAsset('file.txt');
    $svg = $this->makeEmptyAsset('file.svg');

    $this->service->shouldReceive('enabled')->twice()->andReturn(true);
    $this->service->shouldReceive('dispatch')->never();

    (new GeneratePlaceholder($this->service))->handle(new AssetUploaded($txt, null));
    (new GeneratePlaceholder($this->service))->handle(new AssetReuploaded($svg, null));
});
