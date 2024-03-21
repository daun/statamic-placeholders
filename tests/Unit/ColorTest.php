<?php

use Daun\StatamicPlaceholders\Support\Color;

test('converts hex to rgba', function () {
    expect(Color::hexToRgba('#000000'))->toEqual([0, 0, 0, 255]);
    expect(Color::hexToRgba('#ff0000'))->toEqual([255, 0, 0, 255]);
    expect(Color::hexToRgba('#00ff00'))->toEqual([0, 255, 0, 255]);
    expect(Color::hexToRgba('#0000FF00'))->toEqual([0, 0, 255, 0]);
    expect(Color::hexToRgba('#0000ffAA'))->toEqual([0, 0, 255, 170]);
});

test('converts rgba to hex', function () {
    expect(Color::rgbaToHex([0, 0, 0, 255]))->toBe('#000000ff');
    expect(Color::rgbaToHex([255, 0, 0, 255]))->toBe('#ff0000ff');
    expect(Color::rgbaToHex([0, 255, 0, 255]))->toBe('#00ff00ff');
    expect(Color::rgbaToHex([0, 0, 255, 0]))->toBe('#0000ff00');
    expect(Color::rgbaToHex([0, 0, 255, 170]))->toBe('#0000ffaa');
});
