<?php

namespace Daun\StatamicPlaceholders\Commands;

use Daun\StatamicPlaceholders\Commands\Concerns\HasOutputStyles;
use Daun\StatamicPlaceholders\Services\PlaceholderService;
use Daun\StatamicPlaceholders\Support\PlaceholderField;
use Daun\StatamicPlaceholders\Support\Queue;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Statamic\Assets\AssetContainer;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Asset as AssetFacade;
use Statamic\Facades\AssetContainer as AssetContainerFacade;

class Generate extends Command
{
    use HasOutputStyles;
    use RunsInPlease;

    protected $signature = 'statamic:placeholders:generate
                        {--container= : Limit the command to a specific asset container}
                        {--force : Regenerate placeholders even if they already exist}';

    protected $description = 'Generate placeholder images';

    protected $container;

    protected $force;

    protected $sync;

    public function handle(PlaceholderService $service): void
    {
        $this->container = $this->option('container');
        $this->force = $this->option('force');
        $this->sync = Queue::connection() === 'sync';

        if (! PlaceholderService::enabled()) {
            $this->error('The placeholder feature is globally disabled from your config.');

            return;
        }

        $containers = $this->getContainers();

        $assets = $containers->flatMap(
            fn ($container) => AssetFacade::whereContainer($container->handle())->filter(
                fn ($asset) => PlaceholderField::supportsAssetType($asset)
            )
        );

        if ($assets->count()) {
            $this->generatePlaceholdersForAssets($assets, $service);
        } else {
            $this->line("No images found in containers: <name>{$containers->implode(', ')}</name>");
        }
    }

    protected function generatePlaceholdersForAssets(Collection $assets, PlaceholderService $service): void
    {

        $assetGroups = $assets->mapToGroups(function ($asset) use ($service) {
            $exists = $service->exists($asset);
            $action = ! $exists ? 'generate' : ($this->force ? 'regenerate' : 'skip');

            return [$action => $asset];
        });

        $assetsToGenerate = $assetGroups->get('generate', collect());
        $assetsToRegenerate = $assetGroups->get('regenerate', collect());
        $assetsToSkip = $assetGroups->get('skip', collect());

        $assetsToGenerate->each(function ($asset) use ($service) {
            if ($this->sync) {
                $service->generate($asset);
                $this->line("Generated placeholder of <name>{$asset->id()}</name>");
            } else {
                $service->dispatch($asset);
                $this->line("Queued placeholder generation of <name>{$asset->id()}</name>");
            }
        })->whenNotEmpty(function () {
            $this->newLine();
        });

        $assetsToRegenerate->each(function ($asset) use ($service) {
            if ($this->sync) {
                $service->generate($asset);
                $this->line("Regenerated placeholder of <name>{$asset->id()}</name>");
            } else {
                $service->dispatch($asset);
                $this->line("Queued placeholder regeneration of <name>{$asset->id()}</name>");
            }
        })->whenNotEmpty(function () {
            $this->newLine();
        });

        $assetsToSkip->each(function ($asset) {
            $this->line("Skipped <name>{$asset->id()}</name>");
        })->whenNotEmpty(function () {
            $this->newLine();
        });

        $generated = $assetsToGenerate->count() + $assetsToRegenerate->count();
        $skipped = $assetsToSkip->count();

        if ($this->sync) {
            $this->info("<success>✓ Generated {$generated} placeholders, skipped {$skipped} images</success>");
        } else {
            $this->info("<success>✓ Queued {$generated} images for placeholder generation, skipped {$skipped} images</success>");
        }
    }

    protected function getContainers(): Collection
    {
        // Container argument passed in? Get the specified container

        if ($this->container) {
            $container = AssetContainerFacade::find($this->container);
            if ($container && PlaceholderField::existsInBlueprint($container)) {
                return collect($container);
            } elseif ($container) {
                $this->error("Asset container '{$this->container}' is not configured to generate placeholders.");
            } else {
                $this->error("Asset container '{$this->container}' not found");
            }
            return collect();
        }

        // Otherwise: get all containers with a placeholder field

        $containers = AssetContainerFacade::all()
            ->filter(fn (AssetContainer $container) => PlaceholderField::existsInBlueprint($container))
            ->keyBy->handle();

        if ($containers->isEmpty()) {
            $this->error('No containers are configured to generate placeholders.');
            $this->newLine();
            $this->line('Please add a `placeholder` field to at least one of your asset blueprints.');
        }

        return $containers;
    }
}
