<?php

use Daun\StatamicPlaceholders\Services\ImageManager;

beforeEach(function () {
    $this->manager = $this->app->make(ImageManager::class);
});

test('reports correct driver', function () {
    $this->app['config']->set('statamic.assets.image_manipulation.driver', 'test');
    expect($this->manager->driver())->toBe('test');
});
