<?php

namespace Daun\StatamicPlaceholders\Models\Concerns;

use Illuminate\Support\Facades\Cache;

trait WritesToCache
{
    public function identifier(): string
    {
        throw new \Exception('Identifier not implemented.');
    }

    public function key(): ?string
    {
        return "placeholder-hash--{$this->provider()::$handle}--{$this->identifier()}";
    }

    public function exists(): bool
    {
        return Cache::has($this->key());
    }

    protected function load(): ?string
    {
        return Cache::get($this->key());
    }

    protected function save(string $hash): void
    {
        Cache::set($this->key(), $hash);
    }
}
