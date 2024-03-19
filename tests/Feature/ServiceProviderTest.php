<?php

use Daun\StatamicLatte\ServiceProvider;
use Illuminate\Support\Facades\View;

test('adds addon view namespace', function () {
    $namespaces = View::getFinder()->getHints();
    expect($namespaces)->toHaveKey(ServiceProvider::$temporaryViewNamespace);
});

test('installs default extensions', function () {
    /** @var \Latte\Engine $engine */
    $engine = $this->app->get('latte.engine');
    $extensions = collect($engine->getExtensions())->map(fn ($extension) => get_class($extension));
    expect($extensions)->toContain(...ServiceProvider::$defaultExtensions);
});
