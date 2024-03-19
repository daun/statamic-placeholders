<?php

namespace Daun\StatamicPlaceholders\Data;

use Illuminate\Support\Facades\Cache;

class UrlPlaceholder extends Placeholder
{
    protected ?string $provider;

    public function __construct(
        protected string $url
    ) {
    }

    public function key(): ?string
    {
        return "asset-placeholder-hash--{$this->provider()::$handle}--{$this->url}";
    }

    public function exists(): ?string
    {
        return Cache::has($this->key());
    }

    public function contents(): ?string
    {
        return Cache::rememberForever($this->key(), function () {
            if (($data = @file_get_contents($this->url)) !== false) {
                return $data;
            } else {
                $error = error_get_last();
                throw new \Exception($error['message']);
            }
        });
    }
}
