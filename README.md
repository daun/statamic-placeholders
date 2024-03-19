# Statamic Placeholder Images

Generate image placeholders of Statamic assets for smoother lazyloading.

![Example image placeholders](art/example-placeholder.png)

## ✨ Features

- Generate blurry image placeholders for assets
- Supports [ThumbHash](https://evanw.github.io/thumbhash/), [BlurHash](https://blurha.sh/), and average color placeholders
- Console commands for batch generation

## Why Use Image Placeholders?

Low-Quality Image Placeholders (LQIP) are used to improve the perceived performance of sites by displaying a small, low-quality version of an image while the high-quality version is loading. The LQIP technique is often used in combination with progressive lazyloading.

## How It Works

This addon will automatically generate a small blurry image placeholder for each asset that is uploaded to asset containers configured to use them. In your frontend views, you can access the image placeholder as a data URI string to display while the high-quality image is loading. See below for markup examples.

## Placeholder Types

The addon supports generating various types of image placeholders. The recommended type is `ThumbHash` which encodes most detail and supports transparent images.

### ThumbHash

[ThumbHash](https://evanw.github.io/thumbhash/) is a newer image placeholder algorithm with improved color rendering and support for transparency.

### BlurHash

[BlurHash](https://blurha.sh/) is the original placeholder algorithm, developed at Wolt. It currently has no support for alpha channels and will render transparency in black.

### Average color

Calculates the average color of the image.

## 🛠️ Installation

Run the following command from your project root:

```sh
composer require daun/statamic-placeholders
```

Alternatively, you can search for this addon in the `Tools > Addons` section of
the Statamic control panel and install it from there.

## License

[MIT](https://opensource.org/licenses/MIT)
