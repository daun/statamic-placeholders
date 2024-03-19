<?php

namespace Daun\StatamicPlaceholders\Data;

class EmptyPlaceholder extends Placeholder
{
    public function contents(): ?string
    {
        return null;
    }
}
