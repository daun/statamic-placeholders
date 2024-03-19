<?php

namespace Daun\StatamicPlaceholders\Listeners;

use Daun\StatamicPlaceholders\Services\PlaceholderService;
use Daun\StatamicPlaceholders\Support\PlaceholderField;
use Illuminate\Contracts\Queue\ShouldQueue;
use Statamic\Events\AssetReuploaded;
use Statamic\Events\AssetSaved;
use Statamic\Events\AssetUploaded;

class GeneratePlaceholder implements ShouldQueue
{
    public function __construct(protected PlaceholderService $service)
    {
    }

    public function handle(AssetSaved|AssetUploaded|AssetReuploaded $event)
    {
        if ($this->shouldHandle($event->asset)) {
            $this->service->dispatch($event->asset);
        }
    }

    protected function shouldHandle(mixed $asset)
    {
        return PlaceholderField::generatesOnUpload()
            && PlaceholderField::shouldGenerate($asset)
            && ! $this->service->exists($asset);
    }
}
