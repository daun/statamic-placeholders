<?php

use Daun\StatamicPlaceholders\Services\PlaceholderProviders;
use Daun\StatamicPlaceholders\Services\PlaceholderService;

test('binds provider service', function () {
    expect($this->app[PlaceholderProviders::class])->toBeInstanceOf(PlaceholderProviders::class);
});

test('binds placeholder service', function () {
    expect($this->app[PlaceholderService::class])->toBeInstanceOf(PlaceholderService::class);
});
