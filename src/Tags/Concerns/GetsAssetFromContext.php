<?php

namespace Daun\StatamicPlaceholders\Tags\Concerns;

use Statamic\Assets\Asset;
use Statamic\Facades\Asset as AssetFacade;
use Statamic\Fields\Value;

trait GetsAssetFromContext
{
    protected $assetParams = ['src', 'path', 'asset'];

    /**
     * Get the asset model from a path or id in the context.
     */
    protected function getAssetFromContext($asset = null): ?Asset
    {
        if (! $asset) {
            if ($this->params->hasAny($this->assetParams)) {
                $asset = $this->params->get($this->assetParams);
            } else {
                $asset = $this->context->value('asset');
            }
        }

        if (is_string($asset)) {
            $asset = AssetFacade::find($asset);
        } elseif ($asset instanceof Value) {
            $asset = AssetFacade::find($asset->value());
        }

        if ($asset && $asset instanceof Asset) {
            return $asset;
        } else {
            return null;
        }
    }
}
