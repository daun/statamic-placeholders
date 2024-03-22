<?php

namespace Daun\StatamicPlaceholders\Data;

use Daun\StatamicPlaceholders\Data\Augmentables\AugmentedAssetPlaceholder;
use Daun\StatamicPlaceholders\Models\Placeholder;
use Statamic\Assets\Asset;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Data\ContainsData;
use Statamic\Data\HasAugmentedInstance;

class AssetPlaceholder implements Augmentable
{
    use ContainsData;
    use HasAugmentedInstance;

    protected Placeholder $placeholder;

    public function __construct(protected Asset $asset)
    {
        $this->placeholder = Placeholder::make($asset);
    }

    public function uri(): ?string
    {
        return $this->placeholder->uri();
    }

    public function hash(): ?string
    {
        return $this->placeholder->hash();
    }

    public function type(): string
    {
        return $this->placeholder->type();
    }

    public function exists(): bool
    {
        return $this->placeholder->exists();
    }

    public function newAugmentedInstance(): AugmentedAssetPlaceholder
    {
        return new AugmentedAssetPlaceholder($this);
    }

    public function __toString()
    {
        return $this->uri() ?? '';
    }
}
