<?php

namespace Daun\StatamicPlaceholders\Services;

use Daun\StatamicPlaceholders\Support\Intervention;
use GdImage;
use Illuminate\Contracts\Config\Repository;
use Imagick;
use Intervention\Image\Colors\Rgb\Color as ColorRgb;
use Intervention\Image\Colors\Rgb\Colorspace as ColorspaceRgb;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\DriverInterface;
use Intervention\Image\Interfaces\ImageInterface;

class ImageService
{
    const DRIVER_GD = 'gd';

    const DRIVER_IMAGICK = 'imagick';

    protected ImageManager $manager;

    public function __construct(public Repository $config)
    {
        $this->manager = new ImageManager(
            $this->driverClass(),
            decodeAnimation: false,
            strip: true
        );
    }

    /**
     * The image driver configured for Statamic: "gd", "imagick" or the class name of
     * any other Intervention driver, e.g. the libvips driver.
     */
    public function driver(): string
    {
        $driver = $this->config->get('statamic.assets.image_manipulation.driver') ?? self::DRIVER_GD;

        // Glide also accepts an array of driver options with the driver under the `driver` key
        return is_array($driver) ? ($driver['driver'] ?? self::DRIVER_GD) : $driver;
    }

    /**
     * Resolve the configured driver to an Intervention driver class, the same way Statamic and Glide do.
     */
    public function driverClass(): string
    {
        return match ($driver = $this->driver()) {
            self::DRIVER_GD => GdDriver::class,
            self::DRIVER_IMAGICK => ImagickDriver::class,
            default => $driver,
        };
    }

    public function driverInstance(): DriverInterface
    {
        return Intervention::driver($this->manager);
    }

    public function manager(): ImageManager
    {
        return $this->manager;
    }

    public function make(mixed $image): ImageInterface
    {
        return Intervention::decode($this->manager, $image);
    }

    public function create(int $width, int $height): ImageInterface
    {
        return Intervention::createImage($this->manager, $width, $height);
    }

    public function supports(mixed $image): bool
    {
        return $this->driverInstance()->supports($image);
    }

    /**
     * Normalize an image before reading its pixels, the same way encoding it as 8-bit png would:
     * convert to an RGB-compatible colorspace (e.g. CMYK to sRGB) and quantize to 8 bits per channel.
     */
    public function normalize(ImageInterface $image): ImageInterface
    {
        $native = $image->core()->native();

        if ($native instanceof Imagick) {
            if (! $this->isRgbCompatible($native->getImageColorspace())) {
                $native->transformImageColorspace(Imagick::COLORSPACE_SRGB);
            }
            $native->setImageDepth(8);
        }

        return $image;
    }

    /**
     * Same colorspaces that the ImageMagick png encoder writes without converting to sRGB.
     */
    protected function isRgbCompatible(int $colorspace): bool
    {
        $compatible = [
            Imagick::COLORSPACE_SRGB,
            Imagick::COLORSPACE_RGB,
            Imagick::COLORSPACE_SCRGB,
            Imagick::COLORSPACE_GRAY,
        ];

        if (defined(Imagick::class.'::COLORSPACE_LINEARGRAY')) {
            $compatible[] = constant(Imagick::class.'::COLORSPACE_LINEARGRAY');
        }

        return in_array($colorspace, $compatible, true);
    }

    /**
     * Read all pixels of an image as a flat list of RGBA values from 0 to 255: [r, g, b, a, r, g, b, a, ...].
     * Reads from the native GD or Imagick image if possible, which is a lot faster than reading via Intervention.
     */
    public function pixels(ImageInterface $image): array
    {
        $native = $image->core()->native();

        return match (true) {
            $native instanceof GdImage => $this->pixelsFromGd($native),
            $native instanceof Imagick => $native->exportImagePixels(0, 0, $image->width(), $image->height(), 'RGBA', Imagick::PIXEL_CHAR),
            default => $this->pixelsFromIntervention($image),
        };
    }

    /**
     * Draw opaque pixels onto an image from a flat list of RGB values from 0 to 255: [r, g, b, r, g, b, ...].
     * Writes to the native GD or Imagick image if possible, which is a lot faster than drawing via Intervention.
     */
    public function drawPixels(ImageInterface $image, array $pixels): ImageInterface
    {
        $native = $image->core()->native();

        match (true) {
            $native instanceof GdImage => $this->drawPixelsWithGd($native, $pixels),
            $native instanceof Imagick => $this->drawPixelsWithImagick($native, $pixels),
            default => $this->drawPixelsWithIntervention($image, $pixels),
        };

        return $image;
    }

    protected function pixelsFromGd(GdImage $image): array
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $truecolor = imageistruecolor($image);

        // GD has a 7-bit alpha channel from 0 (opaque) to 127 (transparent)
        $alphas = array_map(fn ($a) => 255 - (int) ceil($a * 255 / 127), range(0, 127));

        $pixels = [];
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $color = imagecolorat($image, $x, $y);
                if ($truecolor) {
                    $pixels[] = ($color >> 16) & 0xFF;
                    $pixels[] = ($color >> 8) & 0xFF;
                    $pixels[] = $color & 0xFF;
                    $pixels[] = $alphas[($color >> 24) & 0x7F];
                } else {
                    $color = imagecolorsforindex($image, $color);
                    $pixels[] = $color['red'];
                    $pixels[] = $color['green'];
                    $pixels[] = $color['blue'];
                    $pixels[] = $alphas[$color['alpha']];
                }
            }
        }

        return $pixels;
    }

    protected function pixelsFromIntervention(ImageInterface $image): array
    {
        $width = $image->width();
        $height = $image->height();

        $pixels = [];
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $color = Intervention::toColorspace(Intervention::colorAt($image, $x, $y), ColorspaceRgb::class);
                array_push($pixels, ...array_slice(Intervention::colorChannels($color), 0, 4));
            }
        }

        return $pixels;
    }

    protected function drawPixelsWithGd(GdImage $image, array $pixels): void
    {
        $width = imagesx($image);
        $height = imagesy($image);
        for ($y = 0, $i = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++, $i += 3) {
                imagesetpixel($image, $x, $y, ($pixels[$i] << 16) | ($pixels[$i + 1] << 8) | $pixels[$i + 2]);
            }
        }
    }

    protected function drawPixelsWithImagick(Imagick $image, array $pixels): void
    {
        // Import as RGBA with an explicit opaque alpha value: importing RGB only would
        // leave the existing alpha channel of the (transparent) canvas untouched
        $rgba = [];
        for ($i = 0, $count = count($pixels); $i < $count; $i += 3) {
            array_push($rgba, $pixels[$i], $pixels[$i + 1], $pixels[$i + 2], 255);
        }

        $width = $image->getImageWidth();
        $height = $image->getImageHeight();
        $image->importImagePixels(0, 0, $width, $height, 'RGBA', Imagick::PIXEL_CHAR, $rgba);
    }

    protected function drawPixelsWithIntervention(ImageInterface $image, array $pixels): void
    {
        $width = $image->width();
        $height = $image->height();
        for ($y = 0, $i = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++, $i += 3) {
                $image->drawPixel($x, $y, new ColorRgb($pixels[$i], $pixels[$i + 1], $pixels[$i + 2]));
            }
        }
    }
}
