<?php

use Tanzar\Refract\Exceptions\RefractException;
use Tanzar\Refract\Support\Bands\Tree\BandsBranch;

test('builds and returns correct values', function () {
    $keys = ['type', 'price'];
    $branch = new BandsBranch(0, $keys);

    $branch->add(1, 10.0, ['type' => 'A', 'price' => 100]);
    $branch->add(2, 20.0, ['type' => 'A', 'price' => 200]); 

    $branch->add(3, 30.0, ['type' => 'B', 'price' => 100]);
    $branch->add(4, 30.0, ['type' => 'B', 'price' => 100]);

    expect($branch->getValue(['type' => 'A', 'price' => 100]))->toBe(10.0);
    expect($branch->getBands(['type' => 'A', 'price' => 100]))->toBe([1]);

    expect($branch->getValue(['type' => 'A', 'price' => 200]))->toBe(20.0);
    expect($branch->getBands(['type' => 'A', 'price' => 200]))->toBe([2]);

    expect($branch->getValue(['type' => 'B', 'price' => 100]))->toBe(60.0);
    expect($branch->getBands(['type' => 'B', 'price' => 100]))->toBe([3, 4]);
    
    expect($branch->getValue(['type' => 'A']))->toBe(30.0);
    expect($branch->getBands(['type' => 'A']))->toBe([1, 2]);
    expect($branch->getValue(['type' => 'B']))->toBe(60.0);
    expect($branch->getBands(['type' => 'B']))->toBe([3, 4]);
    expect($branch->getValue(['type' => 'C']))->toBe(0.0);
    expect($branch->getBands(['type' => 'C']))->toBe([]);

    expect($branch->getValue(['price' => 100]))->toBe(70.0);
    expect($branch->getBands(['price' => 100]))->toBe([1, 3, 4]);
});

test('throws exception for invalid key index', function () {
    $keys = ['type'];
    new BandsBranch(1, $keys);
})->throws(RefractException::class, 'Invalid key index 1 for building branch');

test('throws exception for missing key value', function () {
    $keys = ['type', 'price'];
    $branch = new BandsBranch(0, $keys);
    $branch->add(1, 10.0, ['cost' => 100]);
})->throws(RefractException::class, 'Missing key value for branch key type');
