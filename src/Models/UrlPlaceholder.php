<?php

namespace Daun\StatamicPlaceholders\Models;

use Daun\StatamicPlaceholders\Models\Concerns\WritesToCache;
use Illuminate\Support\Str;

class UrlPlaceholder extends Placeholder
{
    use WritesToCache;

    public function __construct(protected string $url)
    {
    }

    public static function accepts(mixed $input): bool
    {
        return Str::isUrl($input, ['http', 'https']);
    }

    public function identifier(): string
    {
        return $this->url;
    }

    public function contents(): ?string
    {
        $data = @file_get_contents($this->url);
        if ($data !== false) {
            return $data;
        } else {
            $error = error_get_last();
            throw new \Exception($error['message'] ?? 'Failed loading url contents.');
        }
    }
}
