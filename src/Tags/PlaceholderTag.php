<?php

namespace Daun\StatamicPlaceholders\Tags;

use Daun\StatamicPlaceholders\Facades\Placeholders;
use Daun\StatamicPlaceholders\Tags\Concerns\GetsSourceFromContext;
use Statamic\Tags\Concerns\RendersAttributes;
use Statamic\Tags\Tags;

class PlaceholderTag extends Tags
{
    use GetsSourceFromContext;
    use RendersAttributes;

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
    public function uri($asset = null): ?string
    {
        return Placeholders::uri(
            $this->getPlaceholderSourceFromContext($asset),
            $this->params->get('type')
        );
    }

    /**
     * Tag {{ placeholder:hash }}
     *
     * Return the placeholder hash of an asset.
     */
    public function hash($asset = null): ?string
    {
        return Placeholders::hash(
            $this->getPlaceholderSourceFromContext($asset),
            $this->params->get('type')
        );
    }

    /**
     * Tag {{ placeholder:data }} ... {{ /placeholder:data }}.
     *
     * Generate placeholder and make variables available within the pair.
     */
    public function data($asset = null): array
    {
        $asset = $this->getPlaceholderSourceFromContext($asset);
        if (! $asset) {
            return [];
        }

        try {
            $placeholder = Placeholders::make($asset)
                ->usingProvider($this->params->get('type'));

            $data = [
                'hash' => $placeholder->hash(),
                'uri' => $placeholder->uri(),
                'placeholder' => $placeholder->uri(),
                'exists' => $placeholder->exists(),
                'type' => $placeholder->type(),
            ];

            return $data;
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
        $asset = $this->getPlaceholderSourceFromContext();
        if (! $asset) {
            return '';
        }

        $provider = $this->params->get('type');
        $uri = Placeholders::uri($asset, $provider);
        if (! $uri) {
            return null;
        }

        $attributes = collect([
            'aria-hidden' => true,
        ])->merge(
            collect($this->params->all())->except([
                ...$this->assetParams,
                ...$this->urlParams,
                'type',
            ])
        )->whereNotNull()->all();

        return vsprintf('<img src="%s" alt="" %s />', [$uri, $this->renderAttributes($attributes)]);
    }
}
