<?php

use Daun\StatamicPlaceholders\Services\ImageService;

beforeEach(function () {
    $this->service = $this->app->make(ImageService::class);
});

test('reports correct driver', function () {
    $this->app['config']->set('statamic.assets.image_manipulation.driver', 'test');
    expect($this->service->driver())->toBe('test');
});
