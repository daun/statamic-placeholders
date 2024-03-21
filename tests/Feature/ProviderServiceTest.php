<?php

use Daun\StatamicPlaceholders\Contracts\PlaceholderProvider;
use Daun\StatamicPlaceholders\Providers;
use Daun\StatamicPlaceholders\Providers\Blurhash;
use Daun\StatamicPlaceholders\Providers\Thumbhash;
use Daun\StatamicPlaceholders\Services\PlaceholderProviders;
use Illuminate\Support\Collection;

beforeEach(function () {
    $this->providers = $this->app->make(PlaceholderProviders::class);
    $this->defaultProvider = Thumbhash::class;
    $this->coreProviders = collect([
        Providers\Thumbhash::class,
        Providers\Blurhash::class,
        Providers\AverageColor::class,
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
    expect($this->providers->find('blurhash'))->toBeInstanceOf(Blurhash::class);
    expect($this->providers->find(Blurhash::class))->toBeInstanceOf(Blurhash::class);
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
    expect($this->providers->default())->toBeInstanceOf(Blurhash::class);
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

class TestProvider extends PlaceholderProvider
{
    public static string $handle = 'test';

    public static string $name = 'Test';

    public function encode(string $contents): ?string
    {
        return 'test-hash';
    }

    public function decode(string $placeholder, int $width = 0, int $height = 0): ?string
    {
        return 'test-uri';
    }
}

class InvalidProvider
{
    public static string $handle = 'invalid';

    public static string $name = 'Invalid';
}
