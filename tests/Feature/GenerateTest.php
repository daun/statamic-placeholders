<?php

use Daun\StatamicPlaceholders\Listeners\GeneratePlaceholder;
use Daun\StatamicPlaceholders\Services\PlaceholderService;
use Statamic\Events\AssetReuploaded;
use Statamic\Events\AssetUploaded;

beforeEach(function () {
    $this->service = Mockery::mock(PlaceholderService::class);
});

test('checks if service is enabled', function () {
    $asset = $this->makeEmptyAsset("file.jpg");

    $this->service->shouldReceive('enabled')->twice();

    (new GeneratePlaceholder($this->service))->handle(new AssetUploaded($asset));
    (new GeneratePlaceholder($this->service))->handle(new AssetReuploaded($asset));
});

test('does not generate placeholders by default', function () {
    $asset = $this->makeEmptyAsset("file.jpg");

    $this->service->shouldReceive('enabled')->twice();
    $this->service->shouldReceive('generate')->never();

    (new GeneratePlaceholder($this->service))->handle(new AssetUploaded($asset));
    (new GeneratePlaceholder($this->service))->handle(new AssetReuploaded($asset));
});

test('generates placeholders in enabled containers', function () {
    $this->setPlaceholderEnabledAssetContainerBlueprint();

    $asset = $this->makeEmptyAsset("file.jpg");

    $this->service->shouldReceive('enabled')->twice()->andReturn(true);
    $this->service->shouldReceive('exists')->twice()->andReturn(false);
    $this->service->shouldReceive('generate')->twice()->with($asset);

    (new GeneratePlaceholder($this->service))->handle(new AssetUploaded($asset));
    (new GeneratePlaceholder($this->service))->handle(new AssetReuploaded($asset));
});

test('generates no placeholders for existing assets', function () {
    $this->setPlaceholderEnabledAssetContainerBlueprint();

    $asset = $this->makeEmptyAsset("file.jpg");

    $this->service->shouldReceive('enabled')->twice()->andReturn(true);
    $this->service->shouldReceive('exists')->twice()->andReturn(true);
    $this->service->shouldReceive('generate')->never();

    (new GeneratePlaceholder($this->service))->handle(new AssetUploaded($asset));
    (new GeneratePlaceholder($this->service))->handle(new AssetReuploaded($asset));
});

test('generates no placeholders for unsupported assets', function () {
    $this->setPlaceholderEnabledAssetContainerBlueprint();

    $txt = $this->makeEmptyAsset("file.txt");
    $svg = $this->makeEmptyAsset("file.svg");

    $this->service->shouldReceive('enabled')->twice()->andReturn(true);
    $this->service->shouldReceive('generate')->never();

    (new GeneratePlaceholder($this->service))->handle(new AssetUploaded($txt));
    (new GeneratePlaceholder($this->service))->handle(new AssetReuploaded($svg));
});
