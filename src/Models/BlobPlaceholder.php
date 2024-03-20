<?php

namespace Daun\StatamicPlaceholders\Models;

use Daun\StatamicPlaceholders\Models\Concerns\WritesToCache;

/**
 * A placeholder for a raw file blob.
 * Reads and writes the placeholder hash to the cache, keyed by the file's hash.
 */
class BlobPlaceholder extends Placeholder
{
    use WritesToCache;

    public function __construct(protected string $blob)
    {
    }

    public static function accepts(mixed $input): bool
    {
        return is_string($input) && ! empty($input);
    }

    public function identifier(): string
    {
        return md5($this->blob);
    }

    public function contents(): ?string
    {
        return $this->blob;
    }
}
