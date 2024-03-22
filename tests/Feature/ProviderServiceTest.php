<?php

use Daun\StatamicPlaceholders\Contracts\PlaceholderProvider;
use Daun\StatamicPlaceholders\Providers;
use Daun\StatamicPlaceholders\Providers\BlurHash;
use Daun\StatamicPlaceholders\Providers\ThumbHash;
use Daun\StatamicPlaceholders\Services\PlaceholderProviders;
use Illuminate\Support\Collection;
use Tests\Fixtures\InvalidProvider;
use Tests\Fixtures\TestProvider;

beforeEach(function () {
    $this->providers = $this->app->make(PlaceholderProviders::class);
    $this->defaultProvider = ThumbHash::class;
    $this->coreProviders = collect([
        Providers\ThumbHash::class,
        Providers\BlurHash::class,
        Providers\AverageColor::class,
        // Providers\None::class,
    ])->keyBy(fn ($provider) => $provider::$handle);
});

test('returns a collection of providers', function () {
    expect($this->providers->all())->toBeInstanceOf(Collection::class);
    expect($this->providers->all())->each->toBeInstanceOf(PlaceholderProvider::class);
});

test('returns the core providers', function () {
    expect($this->providers->all())
        ->map(fn ($provider) => $provider::class)
        ->toEqual($this->coreProviders);
});

test('returns a default provider', function () {
    expect($this->providers->default())->toBeInstanceOf(PlaceholderProvider::class);
    expect($this->providers->default())->toBeInstanceOf($this->defaultProvider);
});

test('returns a provider by handle or class', function () {
    expect($this->providers->find('blurhash'))->toBeInstanceOf(BlurHash::class);
    expect($this->providers->find(BlurHash::class))->toBeInstanceOf(BlurHash::class);
});

test('returns null for nonexisting providers', function () {
    expect($this->providers->find('doesntexist'))->toBeNull();
});

test('can fail for nonexisting providers', function () {
    expect(fn () => $this->providers->findOrFail('blurhash'))->not->toThrow(\Exception::class);
    expect(fn () => $this->providers->findOrFail('doesntexist'))->toThrow(\Exception::class);
});

test('falls back to default provider', function () {
    expect($this->providers->findOrFail(null))->toBeInstanceOf($this->defaultProvider);
});

test('makes default provider configurable', function () {
    config(['placeholders.default_provider' => 'blurhash']);
    expect($this->providers->default())->toBeInstanceOf(BlurHash::class);
});

test('reads user providers', function () {
    config(['placeholders.providers' => [TestProvider::class]]);
    expect($this->providers->all())
        ->map(fn ($provider) => $provider::class)
        ->toEqual($this->coreProviders->merge(['test' => TestProvider::class]));
});

test('fails for missing user providers', function () {
    config(['placeholders.providers' => ['\Missing\Provider\Class']]);
    expect(fn () => $this->providers->all())->toThrow(\Exception::class);
});

test('fails for invalid user providers', function () {
    config(['placeholders.providers' => [InvalidProvider::class]]);
    expect(fn () => $this->providers->all())->toThrow(\Exception::class);
});
