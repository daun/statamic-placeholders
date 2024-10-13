<?php

namespace Daun\StatamicPlaceholders\GraphQL;

use Daun\StatamicPlaceholders\Data\AssetPlaceholder;
use Statamic\Facades\GraphQL;

class AssetPlaceholderType extends \Rebing\GraphQL\Support\Type
{
    const NAME = 'AssetPlaceholder';

    protected $attributes = [
        'name' => self::NAME,
        'description' => 'Low-quality image placeholder (LQIP)',
    ];

    public function fields(): array
    {
        return [
            'type' => [
                'type' => GraphQL::string(),
                'resolve' => fn (AssetPlaceholder $item) => $item->augmentedValue('type'),
                'description' => 'Type of placeholder used, e.g. thumbhash or blurhash',
            ],
            'hash' => [
                'type' => GraphQL::string(),
                'resolve' => fn (AssetPlaceholder $item) => $item->augmentedValue('hash'),
                'description' => 'Short textual representation of the image',
            ],
            'uri' => [
                'type' => GraphQL::string(),
                'resolve' => fn (AssetPlaceholder $item) => $item->augmentedValue('uri'),
                'description' => 'Ready-to-use data URI of the placeholder image',
            ],
        ];
    }
}
