<?php

use Daun\StatamicPlaceholders\Providers\None;

test('returns an empty hash', function () {
    $provider = $this->app->make(None::class);
    expect($provider->encode('abc'))->toBeNull();
});

test('returns an empty data uri', function () {
    $provider = $this->app->make(None::class);
    expect($provider->decode('def'))->toBeNull();
});
