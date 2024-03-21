<?php

namespace Daun\StatamicPlaceholders\Jobs;

use Daun\StatamicPlaceholders\Services\PlaceholderService;
use Daun\StatamicPlaceholders\Support\Queue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Statamic\Assets\Asset;

class ClearPlaceholderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Asset $asset, protected bool $force = false)
    {
        $this->connection = Queue::connection();
        $this->queue = Queue::queue();
    }

    public function handle(PlaceholderService $service): void
    {
        $service->delete($this->asset);
    }
}
