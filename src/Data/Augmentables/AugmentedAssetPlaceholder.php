<?php

namespace Daun\StatamicPlaceholders\Data\Augmentables;

use Statamic\Data\AbstractAugmented;

class AugmentedAssetPlaceholder extends AbstractAugmented
{
    public function keys()
    {
        return ['uri', 'hash', 'type', 'exists'];
    }
}
