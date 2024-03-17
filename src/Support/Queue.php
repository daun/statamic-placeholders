<?php

namespace Daun\StatamicPlaceholders\Support;

class Queue
{
    public static function connection(): ?string
    {
        return config('placeholders.queue.connection') ?? config('queue.default');
    }

    public static function queue(): ?string
    {
        return config('placeholders.queue.queue', 'default');
    }
}
