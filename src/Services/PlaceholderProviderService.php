<?php

namespace Daun\StatamicPlaceholders\Services;

use Daun\StatamicPlaceholders\Contracts\PlaceholderProvider;
use Daun\StatamicPlaceholders\Providers;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class PlaceholderProviderService
{
    protected string $defaultProvider = Providers\Thumbhash::class;

    protected array $coreProviders = [
        Providers\None::class,
        Providers\AverageColor::class,
        Providers\Blurhash::class,
        Providers\Thumbhash::class,
    ];

    protected array $userProviders;

    protected Collection $providers;

    public function __construct(
        protected Application $app,
        protected Repository $config
    ) {
        $this->userProviders = $this->getUserProviders();
        $this->providers = $this->makeProviders();
    }

    public function all(): Collection
    {
        return $this->providers;
    }

    public function find(string $name, bool $fallback = true): ?PlaceholderProvider
    {
        return $this->providers->get($name) ?? ($fallback ? $this->default() : null);
    }

    public function default(): PlaceholderProvider
    {
        return $this->providers->get($this->defaultProvider::name);
    }

    protected function getUserProviders(): array
    {
        return Arr::wrap($this->config->get('placeholders.providers', []));
    }

    protected function makeProviders(): Collection
    {
        return collect($this->providers)
            ->concat($this->userProviders)
            ->filter(fn ($provider) => $this->isValidProvider($provider))
            ->mapWithKeys(fn ($provider) => [$provider::$name => $this->app->make($provider)]);
    }

    protected function isValidProvider(string $class): bool
    {
        return class_exists($class) && in_array(PlaceholderProvider::class, class_implements($class));
    }
}
