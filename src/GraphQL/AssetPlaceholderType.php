<?php

namespace Daun\StatamicPlaceholders\GraphQL;

use Daun\StatamicPlaceholders\Fieldtypes\PlaceholderFieldtype;
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
                'resolve' => fn (PlaceholderFieldtype $field) => $field->augment('type'),
            ],
            'hash' => [
                'type' => GraphQL::string(),
                'resolve' => fn (PlaceholderFieldtype $field) => $field->augment('hash'),
            ],
            'uri' => [
                'type' => GraphQL::string(),
                'resolve' => fn (PlaceholderFieldtype $field) => $field->augment('uri'),
            ],
            'exists' => [
                'type' => GraphQL::boolean(),
                'resolve' => fn (PlaceholderFieldtype $field) => $field->augment('exists'),
            ],
        ];
    }
}
