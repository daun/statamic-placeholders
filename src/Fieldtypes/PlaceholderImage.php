<?php

namespace Daun\StatamicPlaceholders\Fieldtypes;

use Daun\StatamicPlaceholders\Facades\Placeholders;
use Daun\StatamicPlaceholders\Jobs\GeneratePlaceholderJob;
use Statamic\Contracts\Assets\Asset;
use Statamic\Fields\Fieldtype;

class PlaceholderImage extends Fieldtype
{
    protected static $handle = 'placeholder_image';

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
                'display' => __('statamic-placeholder-images::fieldtypes.placeholder_image.config.placeholder_type.display'),
                'instructions' => __('statamic-placeholder-images::fieldtypes.placeholder_image.config.placeholder_type.instructions'),
                'type' => 'select',
                'default' => 'default',
                'options' => $providers->prepend("Use site default ({$default::$name})", 'default')->all(),
            ],
            'generate_on_upload' => [
                'display' => __('statamic-placeholder-images::fieldtypes.placeholder_image.config.generate_on_upload.display'),
                'instructions' => __('statamic-placeholder-images::fieldtypes.placeholder_image.config.generate_on_upload.instructions'),
                'type' => 'toggle',
            ],
            'preview_placeholder' => [
                'display' => __('statamic-placeholder-images::fieldtypes.placeholder_image.config.preview_placeholder.display'),
                'instructions' => __('statamic-placeholder-images::fieldtypes.placeholder_image.config.preview_placeholder.instructions'),
                'type' => 'toggle',
            ],
        ];
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
        $type = $this->config('placeholder_type', null);
        $provider = Placeholders::providers()->find($type) ?? Placeholders::providers()->default();
        $hash = $asset && Placeholders::exists($asset) ? Placeholders::hash($asset) : null;
        $uri = $asset && Placeholders::exists($asset) ? Placeholders::uri($asset) : null;

        return [
            'is_asset' => (bool) $asset,
            'is_supported' => $supported,
            'provider' => [
                'handle' => $provider::$handle,
                'name' => $provider::$name
            ],
            'hash' => $hash,
            'uri' => $uri,
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
        return Placeholders::generate($value);
    }
}
