<?php

use Daun\StatamicPlaceholders\Jobs\ClearPlaceholderJob;
use Daun\StatamicPlaceholders\Services\PlaceholderService;

beforeEach(function () {
    $this->service = Mockery::mock(PlaceholderService::class);
});

test('deletes placeholders in enabled containers', function () {
    $this->addPlaceholderFieldToAssetBlueprint();
    $asset = $this->makeEmptyAsset('file.jpg');
    $this->service->shouldReceive('delete')->once();

    (new ClearPlaceholderJob($asset))->handle($this->service);
});
