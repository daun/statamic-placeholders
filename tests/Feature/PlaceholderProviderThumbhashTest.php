<?php

use Daun\StatamicPlaceholders\Providers\Thumbhash;

beforeEach(function () {
    $this->thumbhash = new Thumbhash();
    $this->defaultProvider = Thumbhash::class;
});

test('creates a thumbhash', function () {
    $thumbhash = new Thumbhash();
    $content = file_get_contents(fixtures_path('testfiles/test.jpg'));
    $hash = file_get_contents(snapshots_path('placeholders/test.jpg.thumbhash.hash.txt'));
    expect($hash)->toBeString()->not->toBeEmpty();
    expect($thumbhash->encode($content))->toEqual($hash);
});

test('creates a data uri', function () {
    $thumbhash = new Thumbhash();
    $hash = file_get_contents(snapshots_path('placeholders/test.jpg.thumbhash.hash.txt'));
    $uri = file_get_contents(snapshots_path('placeholders/test.jpg.thumbhash.uri.txt'));
    expect($hash)->toBeString()->not->toBeEmpty();
    expect($uri)->toBeString()->not->toBeEmpty();
    expect($thumbhash->decode($hash))->toEqual($uri);
});

