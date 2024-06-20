<?php

namespace Daun\StatamicPlaceholders\Models;

/**
 * An empty placeholder.
 * Acts as a fallback for missing inputs.
 */
class EmptyPlaceholder extends Placeholder
{
    public function __construct(protected mixed $value) {}

    public static function accepts(mixed $input): bool
    {
        return empty($input);
    }

    public function contents(): ?string
    {
        return null;
    }
}
