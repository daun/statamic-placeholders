<?php

namespace Daun\StatamicPlaceholders\Commands;

use Daun\StatamicPlaceholders\Commands\Concerns\HasOutputStyles;
use Daun\StatamicPlaceholders\Services\PlaceholderService;
use Daun\StatamicPlaceholders\Support\Queue;
use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Asset;

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
        $this->containers = collect($this->option('container') ?: $service->containers());
        $this->force = $this->option('force');
        $this->sync = Queue::connection() === 'sync';

        $assets = $this->containers->flatMap(fn ($container) => Asset::whereContainer($container));

        if ($assets->isEmpty()) {
            $this->line("No images found in containers: <file>{$this->containers->implode(', ')}</file>");

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
                $this->line("Generated placeholder of <file>{$asset->id()}</file>");
            } else {
                $service->dispatch($asset);
                $this->line("Queued placeholder generation of <file>{$asset->id()}</file>");
            }
        });

        $assetsToRegenerate->each(function ($asset) use ($service) {
            if ($this->sync) {
                $service->generate($asset);
                $this->line("Regenerated placeholder of <file>{$asset->id()}</file>");
            } else {
                $service->dispatch($asset);
                $this->line("Queued placeholder regeneration of <file>{$asset->id()}</file>");
            }
        })->whenNotEmpty(function () {
            $this->newLine();
        });

        $assetsToSkip->each(function ($asset) {
            $this->line("Skipped <file>{$asset->id()}</file>");
        });

        $this->newLine();

        $generated = $assetsToGenerate->count() + $assetsToRegenerate->count();
        $skipped = $assetsToSkip->count();

        if ($this->sync) {
            $this->info("<success>✓ Generated {$generated} placeholders, skipped {$skipped} images</success>");
        } else {
            $this->info("<success>✓ Queued {$generated} images for placeholder generation, skipped {$skipped} images</success>");
        }
    }
}
