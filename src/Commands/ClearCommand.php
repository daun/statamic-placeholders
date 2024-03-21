<?php

namespace Daun\StatamicPlaceholders\Commands;

use Daun\StatamicPlaceholders\Commands\Concerns\HasOutputStyles;
use Daun\StatamicPlaceholders\Jobs\ClearPlaceholderJob;
use Daun\StatamicPlaceholders\Services\PlaceholderService;
use Daun\StatamicPlaceholders\Support\PlaceholderField;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Statamic\Assets\Asset;
use Statamic\Assets\AssetContainer;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Asset as AssetFacade;
use Statamic\Facades\AssetContainer as AssetContainerFacade;

class ClearCommand extends Command
{
    use HasOutputStyles;
    use RunsInPlease;

    protected $signature = 'statamic:placeholders:clear
                        {--container= : Limit the command to a specific asset container}';

    protected $description = 'Remove existing placeholder images';

    protected $container;

    public function handle(PlaceholderService $service)
    {
        $this->container = $this->option('container');

        $containers = $this->getContainers();
        if ($containers->count()) {
            $containers->each(function ($container) use ($service) {
                $this->clearPlaceholders($container, $service);
                $this->newLine();
            });

            $this->components->info('All placeholders have been removed.');
        }

        return 0;
    }

    protected function clearPlaceholders(AssetContainer $container, PlaceholderService $service): void
    {
        $assets = AssetFacade::whereContainer($container->handle())
            ->filter(fn ($asset) => PlaceholderField::supportsAssetType($asset));

        if (! $assets->count()) {
            $this->components->info("No images found in container <info>{$container->title()}</info>");

            return;
        } else {
            $this->components->info("Removing placeholders in container <info>{$container->title()}</info>");
        }

        $assets->each(function (Asset $asset) use ($service) {
            $exists = $service->exists($asset);
            $name = "<bold>{$asset->path()}</bold>";
            if ($exists) {
                ClearPlaceholderJob::dispatchSync($asset);
                $this->components->twoColumnDetail($name, '<success>✓ Removed</success>');
            } else {
                $this->components->twoColumnDetail($name, '<exists>✓ Empty</exists>');
            }
        });
    }

    protected function getContainers(): Collection
    {
        // Container argument passed in? Get the specified container

        if ($this->container) {
            $container = AssetContainerFacade::find($this->container);
            if ($container && PlaceholderField::existsInBlueprint($container)) {
                return collect($container);
            } elseif ($container) {
                $this->components->error("Asset container '{$this->container}' is not configured to generate placeholders.");
            } else {
                $this->components->error("Asset container '{$this->container}' not found");
            }

            return collect();
        }

        // Otherwise: get all containers with a placeholder field

        $containers = AssetContainerFacade::all()
            ->filter(fn (AssetContainer $container) => PlaceholderField::existsInBlueprint($container))
            ->sortBy('title')
            ->keyBy->handle();

        if ($containers->isEmpty()) {
            $this->components->error('No containers are configured to generate placeholders.');
            $this->newLine();
            $this->line('Please add a `placeholder` field to at least one of your asset blueprints.');
        }

        return $containers;
    }
}
