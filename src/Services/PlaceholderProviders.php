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
        // Providers\None::class,
    ];

    protected array $userProviders;

    protected string $defaultProvider;

    protected Collection $providers;

    public function __construct(protected Application $app, protected Repository $config)
    {
        $this->userProviders = $this->getUserProviders();
        $this->defaultProvider = $this->getDefaultProvider();
        $this->providers = $this->makeProviders();
    }

    public function all(): Collection
    {
        return $this->providers;
    }

    public function find(?string $needle): ?PlaceholderProvider
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
        if ($provider = $this->find($this->defaultProvider)) {
            return $provider;
        } else {
            throw new \Exception("Placeholder provider not found: {$this->defaultProvider}");
        }
    }

    protected function getDefaultProvider(): string
    {
        return $this->config->get('placeholders.default_provider', 'thumbhash');
    }

    protected function getUserProviders(): array
    {
        return Arr::wrap($this->config->get('placeholders.providers', []));
    }

    protected function makeProviders(): Collection
    {
        return collect($this->coreProviders)
            ->concat($this->getUserProviders())
            ->filter(fn ($provider) => $this->isValidProvider($provider))
            ->mapWithKeys(fn ($provider) => [$provider::$handle => $this->app->make($provider)]);
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
