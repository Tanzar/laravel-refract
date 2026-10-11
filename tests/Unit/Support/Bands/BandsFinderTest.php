<?php

use Tanzar\Refract\Exceptions\RefractException;
use Tanzar\Refract\Support\Bands\Tree\BandsBranch;
use Tanzar\Refract\Support\Bands\Finder\BandsFinder;

test('builds and returns correct values', function () {
    $keys = ['type', 'price'];
    $branch = new BandsBranch(0, $keys);

    $branch->add(1, 10.0, ['type' => 'A', 'price' => 100]);
    $branch->add(2, 20.0, ['type' => 'A', 'price' => 200]); 

    $branch->add(3, 30.0, ['type' => 'B', 'price' => 100]);

    $first = new BandsFinder($branch);
    $first->where('type', 'A');

    expect($first->value())->toBe(30.0);
    expect($first->bands())->toBe([1, 2]);

    $second = new BandsFinder($branch);
    $second->where('type', 'A')
        ->where('price', 100);

    expect($second->value())->toBe(10.0);
    expect($second->bands())->toBe([1]);
});

test('throws exception on invalid key', function() {
    $keys = ['type', 'price'];
    $branch = new BandsBranch(0, $keys);

    $first = new BandsFinder($branch);
    $first->where('cat', 'A');
})->throws(RefractException::class, "Key 'cat' is not allowed, use only loaded keys");