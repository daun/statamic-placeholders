<?php

namespace Daun\StatamicPlaceholders\Models;

use Daun\StatamicPlaceholders\Models\Concerns\WritesHashToCache;
use Illuminate\Support\Facades\Http;
use Statamic\Support\Str;

/**
 * A placeholder for a URL pointing to an image.
 * Reads and writes the placeholder hash to the cache, keyed by the url.
 */
class UrlPlaceholder extends Placeholder
{
    use WritesHashToCache;

    public function __construct(protected string $url)
    {
    }

    public static function accepts(mixed $input): bool
    {
        return Str::isUrl($input);
    }

    public function identifier(): string
    {
        return $this->url;
    }

    public function contents(): ?string
    {
        $response = Http::get($this->url);
        if ($response->successful()) {
            return $response->body() ?: null;
        } else {
            throw new \Exception('Failed loading url contents.');
        }
    }
}
