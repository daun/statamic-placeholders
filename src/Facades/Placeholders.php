<?php

namespace Daun\StatamicPlaceholders\Facades;

use Daun\StatamicPlaceholders\PlaceholderService;
use Illuminate\Support\Facades\Facade;

/**
 * @see \Daun\StatamicPlaceholders\PlaceholderService
 */
class Placeholders extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PlaceholderService::class;
    }
}
