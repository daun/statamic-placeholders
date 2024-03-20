<?php

namespace Daun\StatamicPlaceholders\Listeners;

use Daun\StatamicPlaceholders\Services\PlaceholderService;
use Daun\StatamicPlaceholders\Support\PlaceholderField;
use Statamic\Events\AssetReuploaded;
use Statamic\Events\AssetSaved;
use Statamic\Events\AssetUploaded;

class GeneratePlaceholder
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
        return $this->service->enabled()
            && PlaceholderField::supportsAssetType($asset)
            && PlaceholderField::existsInBlueprint($asset)
            && PlaceholderField::generatesOnUpload()
            && ! $this->service->exists($asset);
    }
}
