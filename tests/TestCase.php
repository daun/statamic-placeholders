<?php

namespace Tests;

use Daun\StatamicPlaceholders\ServiceProvider as AddonServiceProvider;
use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Statamic\Extend\Manifest;
use Statamic\Providers\StatamicServiceProvider;
use Statamic\Statamic;
use Tests\Concerns\ResolvesStatamicConfig;

abstract class TestCase extends OrchestraTestCase
{
    use ResolvesStatamicConfig;
    use InteractsWithViews;

    protected function getPackageProviders($app)
    {
        return [
            AddonServiceProvider::class,
            StatamicServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Statamic' => Statamic::class,
        ];
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        // Custom view directory
        $app['config']->set('view.paths', [fixtures_path('views')]);

        // Pull in statamic default config
        $this->resolveStatamicConfiguration($app);

        // Rewrite content paths to use our test fixtures
        $this->resolveStacheStores($app);

        // Set user repository to default flat file system
        $app['config']->set('statamic.users.repository', 'file');

        // Assume pro edition for our tests
        // $app['config']->set('statamic.editions.pro', true);
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $this->registerStatamicAddon($app);
    }

    protected function registerStatamicAddon($app)
    {
        $app->make(Manifest::class)->manifest = [
            'daun/statamic-placeholder-images' => [
                'id' => 'daun/statamic-placeholder-images',
                'namespace' => 'Daun\\StatamicPlaceholders',
            ],
        ];
    }
}
