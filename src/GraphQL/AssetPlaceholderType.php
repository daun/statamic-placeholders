<?php

namespace Daun\StatamicPlaceholders\GraphQL;

use Daun\StatamicPlaceholders\Data\AssetPlaceholder;
use Statamic\Facades\GraphQL;

class AssetPlaceholderType extends \Rebing\GraphQL\Support\Type
{
    const NAME = 'AssetPlaceholder';

    protected $attributes = [
        'name' => self::NAME,
    ];

    public function fields(): array
    {
        return [
            'type' => [
                'type' => GraphQL::string(),
                'resolve' => fn (AssetPlaceholder $item) => $item->augmentedValue('type'),
            ],
            'hash' => [
                'type' => GraphQL::string(),
                'resolve' => fn (AssetPlaceholder $item) => $item->augmentedValue('hash'),
            ],
            'uri' => [
                'type' => GraphQL::string(),
                'resolve' => fn (AssetPlaceholder $item) => $item->augmentedValue('uri'),
            ],
        ];
    }
}
