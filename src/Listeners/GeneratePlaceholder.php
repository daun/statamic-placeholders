<?php

namespace Daun\StatamicPlaceholders\Listeners;

use Daun\StatamicPlaceholders\Listeners\Concerns\UsesAddonQueue;
use Daun\StatamicPlaceholders\Services\PlaceholderService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Statamic\Events\AssetReuploaded;
use Statamic\Events\AssetSaved;
use Statamic\Events\AssetUploaded;

class GeneratePlaceholder implements ShouldQueue
{
    use UsesAddonQueue;

    public function __construct(
        protected PlaceholderService $service
    ) {
    }

    public function handle(AssetSaved|AssetUploaded|AssetReuploaded $event)
    {
        $this->service->handleAssetUpload($event->asset);
    }
}
