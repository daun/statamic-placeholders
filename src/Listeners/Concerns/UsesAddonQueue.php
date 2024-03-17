<?php

namespace Daun\StatamicPlaceholders\Listeners\Concerns;

use Daun\StatamicPlaceholders\Support\Queue;

trait UsesAddonQueue
{
    public function viaConnection(): ?string
    {
        return Queue::connection();
    }

    public function viaQueue(): ?string
    {
        return Queue::queue();
    }
}
