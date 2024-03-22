<?php

use Daun\StatamicPlaceholders\Contracts\PlaceholderProvider;
use Daun\StatamicPlaceholders\Models\AssetPlaceholder;
use Daun\StatamicPlaceholders\Models\BlobPlaceholder;
use Daun\StatamicPlaceholders\Models\EmptyPlaceholder;
use Daun\StatamicPlaceholders\Models\Placeholder;
use Daun\StatamicPlaceholders\Models\UrlPlaceholder;
use Daun\StatamicPlaceholders\Providers\AverageColor;
use Illuminate\Support\Facades\Http;
use Tests\Fixtures\TestProvider;

beforeEach(function () {
    config(['placeholders.providers' => [TestProvider::class]]);

    $this->placeholder = Mockery::mock(Placeholder::class)->makePartial()->shouldAllowMockingProtectedMethods();
    $this->placeholder->usingProvider('test');
});

test('returns its type', function () {
    expect($this->placeholder->provider())->toBeInstanceOf(PlaceholderProvider::class);
    expect($this->placeholder->type())->toBe('test');

    $this->placeholder->usingProvider('average');
    expect($this->placeholder->provider())->toBeInstanceOf(AverageColor::class);
    expect($this->placeholder->type())->toBe('average');
});

test('generates placeholders', function () {
    $this->placeholder->shouldReceive('contents')->once()->andReturn('test-content');
    $this->placeholder->shouldReceive('encode')->once()->andReturn('test-hash');
    $this->placeholder->shouldReceive('save')->once()->with('test-hash');

    expect($this->placeholder->generate())->toBe('test-hash');
});

test('does not generate if disabled', function () {
    config(['placeholders.enabled' => false]);

    $this->placeholder->shouldReceive('load')->once();
    $this->placeholder->shouldReceive('encode')->never();
    $this->placeholder->shouldReceive('save')->never();

    $this->placeholder->generate();
});

test('generates a hash', function () {
    $this->placeholder->shouldReceive('load')->once();
    $this->placeholder->shouldReceive('generate')->once()->andReturn('test-hash');

    expect($this->placeholder->hash())->toBe('test-hash');
});

test('attempts to load a hash first', function () {
    $this->placeholder->shouldReceive('load')->once()->andReturn('loaded');
    $this->placeholder->shouldReceive('generate')->never();

    expect($this->placeholder->hash())->toBe('loaded');
});

test('falls back to fallback uri', function () {
    $this->placeholder->shouldReceive('hash')->once()->andReturn(null);
    $this->placeholder->shouldReceive('fallback')->once()->andReturn('fallback');

    expect($this->placeholder->uri())->toBe('fallback');
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

    $this->addPlaceholderFieldToAssetBlueprint();
    $asset = $this->makeEmptyAsset('file.jpg');
    expect(AssetPlaceholder::accepts($asset))->toBeTrue();
    expect(AssetPlaceholder::accepts('string'))->toBeFalse();
    expect(AssetPlaceholder::accepts(null))->toBeFalse();
});

test('creates appropriate placeholder type', function () {
    $this->addPlaceholderFieldToAssetBlueprint();
    $asset = $this->makeEmptyAsset('file.jpg');

    expect(Placeholder::make(null))->toBeInstanceOf(EmptyPlaceholder::class);
    expect(Placeholder::make('https://example.com/image.jpg'))->toBeInstanceOf(UrlPlaceholder::class);
    expect(Placeholder::make('jdsfnajkdhfkjsadf'))->toBeInstanceOf(BlobPlaceholder::class);
    expect(Placeholder::make($asset))->toBeInstanceOf(AssetPlaceholder::class);
});

test('fetches url content', function () {
    Http::fake(['*' => Http::response('content', 200)]);

    $url = 'https://example.com/image.jpg';
    $placeholder = new UrlPlaceholder($url);

    expect($placeholder->contents())->toBe('content');
    Http::assertSent(fn ($request) => $request->url() === $url);
});

test('caches url hashes', function () {
    Http::fake(['*' => Http::response('content', 200)]);

    $url = 'https://example.com/image.jpg';
    $placeholder = (new UrlPlaceholder($url))->usingProvider('test');

    expect($placeholder->hash())->toBe('test-hash');
    expect($placeholder->hash())->toBe('test-hash');

    Http::assertSentCount(1);
});

test('returns blob content', function () {
    expect((new BlobPlaceholder('test'))->contents())->toBe('test');
});

test('returns empty content', function () {
    expect((new EmptyPlaceholder(null))->contents())->toBe(null);
    expect((new EmptyPlaceholder(''))->contents())->toBe(null);
    expect((new EmptyPlaceholder([]))->contents())->toBe(null);
});
