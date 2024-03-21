<?php

namespace Daun\StatamicPlaceholders\Commands;

use Daun\StatamicPlaceholders\Commands\Concerns\HasOutputStyles;
use Daun\StatamicPlaceholders\Services\PlaceholderService;
use Daun\StatamicPlaceholders\Support\PlaceholderField;
use Daun\StatamicPlaceholders\Support\Queue;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Statamic\Assets\Asset;
use Statamic\Assets\AssetContainer;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Asset as AssetFacade;
use Statamic\Facades\AssetContainer as AssetContainerFacade;

class GenerateCommand extends Command
{
    use HasOutputStyles;
    use RunsInPlease;

    protected $signature = 'statamic:placeholders:generate
                        {--container= : Limit the command to a specific asset container}
                        {--force : Regenerate placeholders even if they already exist}
                        {--queue : Queue the placeholder generation}';

    protected $description = 'Generate placeholder images';

    protected $container;

    protected $force;

    protected $shouldQueue;

    public function handle(PlaceholderService $service)
    {
        if (! $service->enabled()) {
            $this->components->error('The placeholder feature is disabled from <info>config/placeholders.php</info>.');

            return Command::FAILURE;
        }

        $this->container = $this->option('container');
        $this->force = $this->option('force');
        $this->shouldQueue = $this->option('queue');

        if ($this->shouldQueue && Queue::connection() === 'sync') {
            $this->components->error('The queue connection is set to "sync". Queueing will be disabled.');
            $this->shouldQueue = false;
        }

        $containers = $this->getContainers();
        if ($containers->count()) {
            $containers->each(function ($container) use ($service) {
                $this->generatePlaceholders($container, $service);
                $this->newLine();
            });

            $this->components->info(
                $this->shouldQueue
                    ? 'All placeholders have been queued for generation.'
                    : 'All placeholders have been generated.'
            );
        }

        return Command::SUCCESS;
    }

    protected function generatePlaceholders(AssetContainer $container, PlaceholderService $service): void
    {
        $assets = AssetFacade::whereContainer($container->handle())
            ->filter(fn ($asset) => PlaceholderField::supportsAssetType($asset));

        if (! $assets->count()) {
            $this->components->info("No images found in container <info>{$container->title()}</info>");

            return;
        } else {
            $this->components->info("Generating placeholders for container <info>{$container->title()}</info>");
        }

        $assets->each(function (Asset $asset) use ($service) {
            $exists = $service->exists($asset);
            $name = "<bold>{$asset->path()}</bold>";

            if ($exists && ! $this->force) {
                $this->components->twoColumnDetail($name, '<exists>✓ Found</exists>');

                return;
            }
            $service->dispatch($asset, $this->force);
            if ($this->shouldQueue) {
                $this->components->twoColumnDetail($name, '<success>✓ Queued</success>');
            } else {
                $this->components->twoColumnDetail($name, '<success>✓ Generated</success>');
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
