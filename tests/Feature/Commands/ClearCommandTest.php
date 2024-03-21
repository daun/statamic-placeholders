<?php

use Daun\StatamicPlaceholders\Commands\ClearCommand;
use Daun\StatamicPlaceholders\Facades\Placeholders;
use Daun\StatamicPlaceholders\Jobs\ClearPlaceholderJob;
use Daun\StatamicPlaceholders\Support\PlaceholderData;
use Illuminate\Support\Facades\Queue;
use Statamic\Facades\Stache;

beforeEach(function () {
    $this->addPlaceholderFieldToAssetBlueprint();
    $this->asset1 = $this->uploadTestImageToTestContainer('test.jpg');
    $this->asset2 = $this->uploadTestImageToTestContainer('test.png');
    PlaceholderData::clear($this->asset1);
    PlaceholderData::clear($this->asset2);
    Stache::clear();
    Queue::fake();
});

test('requires asset containers with fieldtype', function () {
    $this->restoreDefaultAssetBlueprint();

    $this->artisan(ClearCommand::class)
        ->expectsOutputToContain('No containers are configured')
        ->doesntExpectOutputToContain('Removing placeholders')
        ->assertSuccessful();

    Queue::assertNothingPushed();
});

it('dispatches jobs for all images', function () {
    Placeholders::generate($this->asset1);
    Placeholders::generate($this->asset2);

    $this->artisan(ClearCommand::class)
        ->expectsOutputToContain('Removing placeholders')
        ->assertSuccessful();

    Queue::assertPushed(ClearPlaceholderJob::class, 2);
});
