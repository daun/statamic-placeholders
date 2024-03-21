<?php

use Daun\StatamicPlaceholders\Commands\GenerateCommand;
use Daun\StatamicPlaceholders\Jobs\GeneratePlaceholderJob;
use Daun\StatamicPlaceholders\Support\PlaceholderData;
use Illuminate\Support\Facades\Queue;
use Statamic\Facades\Stache;

beforeEach(function () {
    $this->addPlaceholderFieldToAssetBlueprint();
    $this->asset1 = $this->uploadTestImageToTestContainer('test.jpg');
    $this->asset2 = $this->uploadTestImageToTestContainer('test.jpg', 'test2.jpg');
    PlaceholderData::clear($this->asset1);
    PlaceholderData::clear($this->asset2);
    Stache::clear();
    Queue::fake();
});

test('requires placeholders to be enabled', function () {
    $this->app['config']->set('placeholders.enabled', false);

    $this->artisan(GenerateCommand::class)
        ->expectsOutputToContain('The placeholder feature is disabled')
        ->doesntExpectOutputToContain('Generating placeholders')
        ->assertFailed();

    Queue::assertNothingPushed();
});

test('requires asset containers with fieldtype', function () {
    $this->restoreDefaultAssetBlueprint();

    $this->artisan(GenerateCommand::class)
        ->expectsOutputToContain('No containers are configured')
        ->doesntExpectOutputToContain('Generating placeholders')
        ->assertSuccessful();

    Queue::assertNothingPushed();
});

it('dispatches jobs for all images', function () {
    $this->artisan(GenerateCommand::class)
        ->expectsOutputToContain('Generating placeholders')
        ->expectsOutputToContain($this->asset1->basename())
        ->expectsOutputToContain($this->asset2->basename())
        ->assertSuccessful();

    Queue::assertPushed(GeneratePlaceholderJob::class, 2);
});

it('requires force param to regenerate placeholders', function () {
    $this->artisan(GenerateCommand::class);

    Queue::assertPushed(GeneratePlaceholderJob::class, 2);

    PlaceholderData::save($this->asset1, ['thumbhash' => 'abc']);
    PlaceholderData::clear($this->asset2);

    $this->artisan(GenerateCommand::class);

    Queue::assertPushed(GeneratePlaceholderJob::class, 3);

    PlaceholderData::save($this->asset1, ['thumbhash' => 'abc']);
    PlaceholderData::clear($this->asset2);

    $this->artisan(GenerateCommand::class, ['--force' => true]);

    Queue::assertPushed(GeneratePlaceholderJob::class, 5);
});
