<?php

namespace Daun\StatamicPlaceholders\Commands;

use Daun\StatamicPlaceholders\Commands\Concerns\HasOutputStyles;
use Daun\StatamicPlaceholders\Services\PlaceholderService;
use Daun\StatamicPlaceholders\Support\PlaceholderField;
use Daun\StatamicPlaceholders\Support\Queue;
use Illuminate\Console\Command;
use Statamic\Assets\AssetContainer;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Asset as AssetFacade;
use Statamic\Facades\AssetContainer as AssetContainerFacade;

class Generate extends Command
{
    use HasOutputStyles;
    use RunsInPlease;

    protected $signature = 'placeholders:generate
                        {--container= : Limit the command to a specific asset container}
                        {--force : Regenerate placeholders even if they already exist}';

    protected $description = 'Generate placeholder images';

    protected $sync;

    protected $force;

    protected $container;

    protected $containers;

    public function handle(PlaceholderService $service): void
    {
        $this->container = $this->option('container');
        $this->force = $this->option('force');
        $this->sync = Queue::connection() === 'sync';

        if (! PlaceholderService::enabled()) {
            $this->error('The placeholder feature is globally disabled from your config.');

            return;
        }

        $this->containers = AssetContainerFacade::all()
            ->filter(fn (AssetContainer $container) => PlaceholderField::existsInBlueprint($container))
            ->keyBy->handle();

        if ($this->containers->isEmpty()) {
            $this->error('No containers are configured to generate placeholders.');
            $this->newLine();
            $this->line('Please add a `placeholder` field to at least one of your asset blueprints.');

            return;
        }

        if ($this->container) {
            $container = AssetContainerFacade::find($this->container);
            if ($container && PlaceholderField::existsInBlueprint($container)) {
                $this->containers = collect($container);
            } elseif ($container) {
                $this->error("Asset container '{$this->container}' is not configured to generate placeholders.");

                return;
            } else {
                $this->error("Asset container '{$this->container}' not found");

                return;
            }
        }

        $assets = $this->containers->flatMap(
            fn ($container) => AssetFacade::whereContainer($container->handle())->filter(
                fn ($asset) => PlaceholderField::supportsAssetType($asset)
            )
        );

        if ($assets->isEmpty()) {
            $this->line("No images found in containers: <name>{$this->containers->implode(', ')}</name>");

            return;
        }

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
}
