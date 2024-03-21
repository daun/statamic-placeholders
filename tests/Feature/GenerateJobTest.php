<?php

use Daun\StatamicPlaceholders\Jobs\GeneratePlaceholderJob;
use Daun\StatamicPlaceholders\Services\PlaceholderService;

beforeEach(function () {
    $this->service = Mockery::mock(PlaceholderService::class);
});

test('checks if service is enabled', function () {
    $asset = $this->makeEmptyAsset('file.jpg');

    $this->service->shouldReceive('enabled')->once();

    (new GeneratePlaceholderJob($asset))->handle($this->service);
});

test('does not generate placeholders by default', function () {
    $asset = $this->makeEmptyAsset('file.jpg');

    $this->service->shouldReceive('enabled')->once();
    $this->service->shouldReceive('generate')->never();

    (new GeneratePlaceholderJob($asset))->handle($this->service);
});

test('generates placeholders in enabled containers', function () {
    $this->addPlaceholderFieldToAssetBlueprint();

    $asset = $this->makeEmptyAsset('file.jpg');

    $this->service->shouldReceive('enabled')->once()->andReturn(true);
    $this->service->shouldReceive('exists')->once()->andReturn(false);
    $this->service->shouldReceive('generate')->once();

    (new GeneratePlaceholderJob($asset))->handle($this->service);
});

test('generates no placeholders for existing assets', function () {
    $this->addPlaceholderFieldToAssetBlueprint();

    $asset = $this->makeEmptyAsset('file.jpg');

    $this->service->shouldReceive('enabled')->once()->andReturn(true);
    $this->service->shouldReceive('exists')->once()->andReturn(true);
    $this->service->shouldReceive('generate')->never();

    (new GeneratePlaceholderJob($asset))->handle($this->service);
});

test('generates no placeholders for unsupported assets', function () {
    $this->addPlaceholderFieldToAssetBlueprint();

    $txt = $this->makeEmptyAsset('file.txt');
    $svg = $this->makeEmptyAsset('file.svg');

    $this->service->shouldReceive('enabled')->twice()->andReturn(true);
    $this->service->shouldReceive('generate')->never();

    (new GeneratePlaceholderJob($txt))->handle($this->service);
    (new GeneratePlaceholderJob($svg))->handle($this->service);
});
