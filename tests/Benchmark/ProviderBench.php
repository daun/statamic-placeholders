<?php

namespace Tests\Benchmark;

use Daun\StatamicPlaceholders\Contracts\PlaceholderProvider;
use Daun\StatamicPlaceholders\Providers\AverageColor;
use Daun\StatamicPlaceholders\Providers\BlurHash;
use Daun\StatamicPlaceholders\Providers\ThumbHash;
use Daun\StatamicPlaceholders\Services\ImageService;
use Generator;
use Illuminate\Config\Repository;
use PhpBench\Attributes as Bench;

#[Bench\BeforeMethods('setUp')]
#[Bench\ParamProviders(['providers', 'drivers', 'files'])]
#[Bench\Revs(5)]
#[Bench\Iterations(5)]
#[Bench\Warmup(1)]
#[Bench\OutputTimeUnit('milliseconds')]
class ProviderBench
{
    protected PlaceholderProvider $provider;

    protected string $contents;

    protected ?string $hash;

    public function providers(): Generator
    {
        yield 'thumbhash' => ['provider' => ThumbHash::class];
        yield 'blurhash' => ['provider' => BlurHash::class];
        yield 'average' => ['provider' => AverageColor::class];
    }

    public function drivers(): Generator
    {
        foreach ([ImageService::DRIVER_GD, ImageService::DRIVER_IMAGICK] as $driver) {
            if ($this->driverWorks($driver)) {
                yield $driver => ['driver' => $driver];
            }
        }
    }

    public function files(): Generator
    {
        foreach (['jpg', 'png', 'gif'] as $ext) {
            yield $ext => ['file' => "test.{$ext}"];
        }
    }

    public function setUp(array $params): void
    {
        $this->provider = new $params['provider'](static::imageService($params['driver']));
        $this->contents = static::fixture($params['file']);
        $this->hash = $this->provider->encode($this->contents);
    }

    #[Bench\Subject]
    public function encode(): void
    {
        $this->provider->encode($this->contents);
    }

    #[Bench\Subject]
    public function decode(): void
    {
        $this->provider->decode($this->hash);
    }

    protected static function imageService(string $driver): ImageService
    {
        return new ImageService(new Repository([
            'statamic' => ['assets' => ['image_manipulation' => ['driver' => $driver]]],
        ]));
    }

    protected static function fixture(string $file): string
    {
        return file_get_contents(__DIR__."/../__fixtures__/testfiles/{$file}");
    }

    /**
     * Skip drivers that aren't installed or can't decode images in this environment.
     */
    protected function driverWorks(string $driver): bool
    {
        if (! extension_loaded($driver)) {
            return false;
        }

        try {
            static::imageService($driver)->make(static::fixture('test.jpg'));

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
