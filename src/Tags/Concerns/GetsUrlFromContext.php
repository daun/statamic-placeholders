<?php

namespace Daun\StatamicPlaceholders\Tags\Concerns;

use Illuminate\Support\Str;
use Statamic\Fields\Value;

trait GetsUrlFromContext
{
    protected $urlParams = ['url'];

    /**
     * Get the asset model from a path or id in the context.
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
