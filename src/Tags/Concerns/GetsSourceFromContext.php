<?php

namespace Daun\StatamicPlaceholders\Tags\Concerns;

use Illuminate\Support\Str;
use Statamic\Assets\Asset;
use Statamic\Facades\Asset as AssetFacade;
use Statamic\Fields\Value;

trait GetsSourceFromContext
{
    protected array $assetParams = ['src', 'path', 'asset'];

    protected array $urlParams = ['url'];

    /**
     * Get the placeholder source from either:
     * - a `url` passed as a parameter
     * - an `asset` passed as a parameter
     * - an `asset` from the context
     */
    protected function getPlaceholderSourceFromContext($asset = null): Asset|string|null
    {
        return $this->getUrlFromContext($asset) ?? $this->getAssetFromContext($asset);
    }

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

    /**
     * Get an external url from a param.
     */
    protected function getUrlFromContext($url = null): ?string
    {
        if ((! $url || ! is_string($url)) && $this->params->hasAny($this->urlParams)) {
            $url = $this->params->get($this->urlParams);
        }

        if ($url instanceof Value) {
            $url = $url->value();
        }

        if (Str::isUrl($url, ['http', 'https'])) {
            return $url;
        } elseif ($url) {
            throw new \Exception("Invalid URL: {$url}");
        } else {
            return null;
        }
    }
}
