<?php

use Daun\StatamicPlaceholders\Facades\Placeholders;
use Daun\StatamicPlaceholders\Services\PlaceholderProviders;
use Daun\StatamicPlaceholders\Services\PlaceholderService;

test('creates correct facade instance', function () {
    expect(Placeholders::getFacadeRoot())->toBeInstanceOf(PlaceholderService::class);
});

test('returns providers from facade', function () {
    expect(Placeholders::providers())->toBeInstanceOf(PlaceholderProviders::class);
});
