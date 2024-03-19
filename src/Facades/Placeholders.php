<?php

namespace Daun\StatamicPlaceholders\Facades;

use Daun\StatamicPlaceholders\Services\PlaceholderService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Daun\StatamicPlaceholders\Services\PlaceholderProviders providers()
 * @method static \Daun\StatamicPlaceholders\Models\Placeholder make(\Statamic\Assets\Asset|string $input)
 * @method static ?string uri(\Statamic\Assets\Asset|string $asset)
 * @method static ?string hash(\Statamic\Assets\Asset|string $asset)
 * @method static bool exists(\Statamic\Assets\Asset|string $asset)
 * @method static ?string generate(\Statamic\Assets\Asset $asset)
 * @method static void dispatch(\Statamic\Assets\Asset $asset)
 *
 * @see \Daun\StatamicPlaceholders\Services\PlaceholderService
 */
class Placeholders extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PlaceholderService::class;
    }
}
