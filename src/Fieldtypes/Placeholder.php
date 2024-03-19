<?php

namespace Daun\StatamicPlaceholders\Fieldtypes;

use Daun\StatamicPlaceholders\Facades\Placeholders;
use Daun\StatamicPlaceholders\Jobs\GeneratePlaceholderJob;
use Daun\StatamicPlaceholders\Support\PlaceholderFieldtype;
use Illuminate\Support\Number;
use Statamic\Contracts\Assets\Asset;
use Statamic\Fields\Fieldtype;

class Placeholder extends Fieldtype
{
    protected static $handle = 'placeholder';

    protected static $title = 'Placeholder Image';

    protected $categories = ['media', 'special'];

    protected $icon = 'assets';

    protected $validatable = false;

    protected function configFieldItems(): array
    {
        $default = Placeholders::providers()->default();
        $providers = Placeholders::providers()->all()->mapWithKeys(
            fn ($provider) => [$provider::$handle => $provider::$name]
        );

        return [
            'placeholder_type' => [
                'display' => __('statamic-placeholders::fieldtypes.placeholder.config.placeholder_type.display'),
                'instructions' => __('statamic-placeholders::fieldtypes.placeholder.config.placeholder_type.instructions'),
                'type' => 'select',
                'placeholder' => "Use site default ({$default::$name})",
                'options' => $providers->prepend("Use site default ({$default::$name})", '')->all(),
            ],
            'preview_placeholder' => [
                'display' => __('statamic-placeholders::fieldtypes.placeholder.config.preview_placeholder.display'),
                'instructions' => __('statamic-placeholders::fieldtypes.placeholder.config.preview_placeholder.instructions'),
                'type' => 'toggle',
            ],
        ];
    }

    public function provider(): ?string
    {
        return $this->config('placeholder_type');
    }

    protected function asset(): ?Asset
    {
        if ($this->field?->parent() instanceof Asset) {
            return $this->field->parent();
        } else {
            return null;
        }
    }

    public function preload()
    {
        $asset = $this->asset();
        $supported = $asset && Placeholders::supports($asset);
        $provider = Placeholders::providers()->find($this->provider()) ?? Placeholders::providers()->default();
        $exists = $asset && Placeholders::exists($asset, $provider::$handle);
        $hash = $exists ? Placeholders::hash($asset, $provider::$handle) : null;
        $uri = $exists ? Placeholders::uri($asset, $provider::$handle) : null;
        $size = $uri ? Number::fileSize(strlen(base64_decode($uri)), 1) : null;

        return [
            'is_asset' => (bool) $asset,
            'is_supported' => $supported,
            'generate_on_upload' => PlaceholderFieldtype::generatesOnUpload(),
            'provider' => [
                'handle' => $provider::$handle,
                'name' => $provider::$name
            ],
            'hash' => $hash,
            'uri' => $uri,
            'size' => $size,
        ];
    }

    public function preProcess($data)
    {
        return ['generate' => false] + ($data ?? []);
    }

    public function process($data)
    {
        $asset = $this->asset();
        $generate = $data['generate'] ?? false;
        unset($data['generate']);

        // (Re)generate placeholder if checkbox was checked by editor
        if ($asset && $generate) {
            GeneratePlaceholderJob::dispatch($asset);
        }

        return $data;
    }

    public function augment($value)
    {
        return Placeholders::uri($this->asset(), $this->provider());
    }
}
