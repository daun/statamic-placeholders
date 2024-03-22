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
        return "placeholder-hash--{$this->type()}--{$this->identifier()}";
    }

    public function exists(): bool
    {
        return (bool) Cache::has($this->key());
    }

    protected function load(): ?string
    {
        if (Cache::has($key = $this->key())) {
            return Cache::get($key);
        } else {
            return null;
        }
    }

    protected function save(?string $hash): void
    {
        Cache::set($this->key(), $hash);
    }
}
