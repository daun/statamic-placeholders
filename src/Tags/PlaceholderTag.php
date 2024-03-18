<?php

namespace Daun\StatamicPlaceholders\Tags;

use Daun\StatamicPlaceholders\Facades\Placeholders;
use Daun\StatamicPlaceholders\Tags\Concerns\GetsAssetFromContext;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Tags\Tags;

class PlaceholderTag extends Tags
{
    use GetsAssetFromContext;

    protected static $handle = 'placeholder';

    /**
     * Tag {{ placeholder:[field] }}
     *
     * Where `field` is the variable containing the image asset
     */
    public function __call($method, $args)
    {
        $tag = explode(':', $this->tag, 2)[1];

        $item = $this->context->value($tag);

        if ($this->isPair) {
            return $this->data($item);
        } else {
            return $this->uri($item);
        }
    }

    /**
     * Tag {{ placeholder }}.
     *
     * Alternate syntax, where you pass the ID or path directly as a parameter or tag pair content
     */
    public function index()
    {
        if ($this->isPair) {
            return $this->data();
        } else {
            return $this->uri();
        }
    }

    /**
     * Tag {{ placeholder:uri }}
     *
     * Return the placeholder data uri of an asset.
     */
    public function uri(): ?string
    {
        return Placeholders::uri($this->getAssetFromContext());
    }

    /**
     * Tag {{ placeholder:hash }}
     *
     * Return the placeholder hash of an asset.
     */
    public function hash(): ?string
    {
        return Placeholders::hash($this->getAssetFromContext());
    }

    /**
     * Tag {{ placeholder:data }} ... {{ /placeholder:data }}.
     *
     * Generate placeholder and make variables available within the pair.
     */
    public function data($asset = null): array
    {
        $asset = $this->getAssetFromContext($asset);
        if (! $asset) {
            return [];
        }

        try {
            $hash = Placeholders::hash($asset);
            $uri = Placeholders::uri($asset);
            $exists = Placeholders::exists($asset);
            // $provider = Placeholders::provider($asset);

            $data = [
                'hash' => $hash,
                'uri' => $uri,
                'exists' => $exists,
                // 'provider' => $provider,
            ];

            if ($asset instanceof Augmentable) {
                return array_merge($asset->toAugmentedArray(), $data);
            } else {
                return $data;
            }
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }

        return [];
    }

    /**
     * Tag {{ placeholder:img }}
     *
     * Return a rendered <img> tag with a placeholder uri.
     */
    public function img(): ?string
    {
        $asset = $this->getAssetFromContext();
        $provider = $this->params->get(['provider', 'type']);
        $uri = Placeholders::uri($asset, $provider);
        if (! $uri) {
            return null;
        }

        $attributes = collect([
                'aria-hidden' => true,
            ])->merge(
                collect($this->params->all())->except([...$this->assetParams, 'provider', 'type'])
            )->whereNotNull()->all();

        return vsprintf('<img src="%s" %s />', [$uri, $this->renderAttributes($attributes)]);
    }
}
