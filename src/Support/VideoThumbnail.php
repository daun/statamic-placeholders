<?php

namespace Daun\StatamicPlaceholders\Support;

use Statamic\Assets\Asset;
use Statamic\Imaging\ThumbnailExtractor;

/**
 * Reuses the video thumbnails Statamic extracts with FFmpeg for the control panel.
 */
class VideoThumbnail
{
    public static function available(): bool
    {
        return ThumbnailExtractor::available();
    }

    /**
     * Get the path of the thumbnail, extracting it first if not cached yet.
     */
    public static function path(Asset $asset): ?string
    {
        if (! $asset->isVideo() || ! static::available()) {
            return null;
        }

        try {
            $path = app(ThumbnailExtractor::class)->generateThumbnail($asset);
        } catch (\Throwable $th) {
            return null;
        }

        return $path && is_file($path) ? $path : null;
    }

    public static function contents(Asset $asset): ?string
    {
        $path = static::path($asset);

        return $path ? (file_get_contents($path) ?: null) : null;
    }
}
