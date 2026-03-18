<?php

use Daun\StatamicPlaceholders\ServiceProvider;
use Daun\StatamicPlaceholders\Services\ImageService;
use Daun\StatamicPlaceholders\Services\PlaceholderProviders;
use Daun\StatamicPlaceholders\Services\PlaceholderService;

test('provides services', function () {
    $provider = new ServiceProvider($this->app);
    expect($provider->provides())->toBeArray()->not->toBeEmpty();
});

test('binds provider service', function () {
    expect($this->app[PlaceholderProviders::class])->toBeInstanceOf(PlaceholderProviders::class);
});

test('binds placeholder service', function () {
    expect($this->app[PlaceholderService::class])->toBeInstanceOf(PlaceholderService::class);
});

test('binds image service', function () {
    expect($this->app[ImageService::class])->toBeInstanceOf(ImageService::class);
});
