<?php

namespace Tests\Concerns;

use ReflectionClass;
use Spatie\Snapshots\MatchesSnapshots;
use Illuminate\Support\Str;

trait HandlesSnapshots
{
    use MatchesSnapshots;

    protected function getSnapshotDirectory(): string
    {
        return snapshots_path();
    }

    protected function getSnapshotId(): string
    {
        return vsprintf('%s--%s--%d', [
            (new ReflectionClass($this))->getShortName(),
            $this->cleanupTestName($this->name()),
            $this->snapshotIncrementor
        ]);
    }

    protected function cleanupTestName(string $name): string
    {
        return (string) Str::of($name)->trim('-_')->remove('pest_')->remove('evaluable_');
    }
}