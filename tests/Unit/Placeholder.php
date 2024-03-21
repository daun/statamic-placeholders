<?php

use Daun\StatamicPlaceholders\Contracts\PlaceholderProvider;
use Daun\StatamicPlaceholders\Models\BlobPlaceholder;
use Daun\StatamicPlaceholders\Models\EmptyPlaceholder;
use Daun\StatamicPlaceholders\Models\UrlPlaceholder;
use Daun\StatamicPlaceholders\Providers\AverageColor;

test('returns its type', function () {
    $placeholder = new UrlPlaceholder('https://example.com/image.jpg');

    expect($placeholder->provider())->toBeInstanceOf(PlaceholderProvider::class);
    expect($placeholder->type())->toBeString();

    $placeholder->usingProvider('average');
    expect($placeholder->provider())->toBeInstanceOf(AverageColor::class);
    expect($placeholder->type())->toBe('average');
});

test('generates if enabled', function () {
    $this->app['config']->set('placeholders.enabled', true);

    $placeholder = Mockery::mock(UrlPlaceholder::class)->makePartial();
    $placeholder->shouldReceive('exists')->once()->andReturn(false);
    $placeholder->shouldReceive('encode')->once();

    $placeholder->generate();
});

test('does not generate if disabled', function () {
    $this->app['config']->set('placeholders.enabled', false);

    $placeholder = Mockery::mock(UrlPlaceholder::class)->makePartial();
    $placeholder->shouldReceive('exists')->once()->andReturn(false);
    $placeholder->shouldReceive('encode')->never();
    $placeholder->shouldReceive('load')->once();

    $placeholder->generate();
});

test('generates a hash', function () {
    $placeholder = Mockery::mock(UrlPlaceholder::class);
    $placeholder->shouldReceive('load')->once()->andReturn(null);
    $placeholder->shouldReceive('generate')->once()->andReturn('generated');

    expect($placeholder->hash())->toBe('generated');
});

test('attempts to load a hash first', function () {
    $placeholder = Mockery::mock(UrlPlaceholder::class);
    $placeholder->shouldReceive('load')->once()->andReturn('loaded');
    $placeholder->shouldReceive('generate')->never();

    expect($placeholder->hash())->toBe('loaded');
});

test('falls back to fallback uri', function () {
    $placeholder = Mockery::mock(UrlPlaceholder::class);
    $placeholder->shouldReceive('hash')->once()->andReturn(null);
    $placeholder->shouldReceive('fallback')->once()->andReturn('fallback');

    expect($placeholder->uri())->toBe('fallback');
});

test('accepts only valid input', function () {
    expect(UrlPlaceholder::accepts(null))->toBeFalse();
    expect(UrlPlaceholder::accepts(''))->toBeFalse();
    expect(UrlPlaceholder::accepts('https://example.com/image.jpg'))->toBeTrue();

    expect(BlobPlaceholder::accepts(null))->toBeFalse();
    expect(BlobPlaceholder::accepts(''))->toBeFalse();
    expect(BlobPlaceholder::accepts('blob'))->toBeTrue();

    expect(EmptyPlaceholder::accepts(null))->toBeTrue();
    expect(EmptyPlaceholder::accepts(''))->toBeTrue();
    expect(EmptyPlaceholder::accepts('full'))->toBeFalse();
});
