<?php

namespace Daun\StatamicPlaceholders\Models;

class EmptyPlaceholder extends Placeholder
{
    public function __construct(...$args)
    {
    }

    public static function accepts(mixed $input): bool
    {
        return true;
    }

    public function contents(): ?string
    {
        return null;
    }
}
