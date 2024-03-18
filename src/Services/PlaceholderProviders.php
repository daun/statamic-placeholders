<?php

namespace Daun\StatamicPlaceholders\Services;

use Daun\StatamicPlaceholders\Contracts\PlaceholderProvider;
use Daun\StatamicPlaceholders\Providers;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class PlaceholderProviders
{
    protected array $coreProviders = [
        Providers\Thumbhash::class,
        Providers\Blurhash::class,
        Providers\AverageColor::class,
        Providers\None::class,
    ];

    protected array $userProviders;

    protected Collection $providers;

    protected string $defaultProvider;

    public function __construct(
        protected Application $app,
        protected Repository $config
    ) {
        $this->defaultProvider = $this->getDefaultProvider();
        $this->userProviders = $this->getUserProviders();
        $this->providers = $this->makeProviders();
    }

    public function all(): Collection
    {
        return $this->providers;
    }

    public function find(?string $needle): PlaceholderProvider
    {
        return $this->providers->first(
            fn ($provider) => in_array($needle, [$provider::class, $provider::$handle])
        );
    }

    public function findOrFail(?string $needle): PlaceholderProvider
    {
        if (! $needle) {
            return $this->default();
        } elseif ($provider = $this->find($needle)) {
            return $provider;
        } else {
            throw new \Exception("Placeholder provider not found: {$needle}");
        }
    }

    public function default(): PlaceholderProvider
    {
        return $this->providers->get($this->defaultProvider::$name) ?? $this->providers->first();
    }

    protected function getDefaultProvider(): string
    {
        $provider = $this->config->get('placeholders.default_provider', Providers\Thumbhash::class);

        return $this->isValidProvider($provider) ? $provider : null;
    }

    protected function getUserProviders(): array
    {
        return Arr::wrap($this->config->get('placeholders.providers', []));
    }

    protected function makeProviders(): Collection
    {
        return collect($this->coreProviders)
            ->concat($this->userProviders)
            ->filter(fn ($provider) => $this->isValidProvider($provider))
            ->mapWithKeys(fn ($provider) => [$provider::$name => $this->app->make($provider)]);
    }

    protected function isValidProvider(string $class): bool
    {
        if (! class_exists($class)) {
            throw new \Exception("Placeholder provider class not found: {$class}");
        }

        if (! is_a($class, PlaceholderProvider::class, true)) {
            throw new \Exception("Not a valid placeholder provider: {$class}");
        }

        return true;
    }
}
