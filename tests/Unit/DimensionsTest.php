<?php

use Daun\StatamicPlaceholders\Support\Dimensions;

test('contains dimensions for landscape', function () {
    expect(Dimensions::contain(100, 50, 10))->toEqual([10, 5]);
    expect(Dimensions::contain(10, 5, 100))->toEqual([10, 5]);
});

test('contains dimensions for portrait', function () {
    expect(Dimensions::contain(50, 100, 10))->toEqual([5, 10]);
    expect(Dimensions::contain(5, 10, 100))->toEqual([5, 10]);
});

test('contains dimensions for square', function () {
    expect(Dimensions::contain(100, 100, 10))->toEqual([10, 10]);
    expect(Dimensions::contain(10, 10, 100))->toEqual([10, 10]);
});
