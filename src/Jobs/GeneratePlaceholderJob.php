<?php

namespace Daun\StatamicPlaceholders\Jobs;

use Daun\StatamicPlaceholders\Services\PlaceholderService;
use Daun\StatamicPlaceholders\Support\PlaceholderField;
use Daun\StatamicPlaceholders\Support\Queue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Statamic\Assets\Asset;

class GeneratePlaceholderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ?PlaceholderService $service = null;

    public function __construct(protected Asset $asset, protected bool $force = false)
    {
        $this->connection = Queue::connection();
        $this->queue = Queue::queue();
    }

    public function handle(PlaceholderService $service): void
    {
        $this->service = $service;

        if ($this->shouldHandle($this->asset)) {
            $this->service->generate($this->asset, $this->force);
        }
    }

    protected function shouldHandle(mixed $asset)
    {
        return $this->service->enabled()
            && PlaceholderField::supportsAssetType($asset)
            && PlaceholderField::existsInBlueprint($asset)
            && (
                $this->force || ! $this->service->exists($asset)
            );
    }
}
