<?php

use Tanzar\Refract\Exceptions\RefractException;
use Tanzar\Refract\Support\Bands\Bands;
use Tanzar\Refract\Support\Bands\Loader\BandsLoaders;
use Tanzar\Refract\Support\Bands\SplitterBands;
use Tanzar\Refract\Support\Bands\Tree\BandsBranch;
use Tanzar\Refract\Support\Filters\Filters;

test('correctly loads multiple different bands', function() {
    $bands = new Bands();

    $cars = new BandsBranch(0, ['type', 'brand']);
    $cars->add(1, 3.0, [ 'type' => 'SUV', 'brand' => 'BMW' ]);
    $cars->add(2, 2.0, [ 'type' => 'SUV', 'brand' => 'Audi' ]);
    $cars->add(3, 4.0, [ 'type' => 'Kombi', 'brand' => 'Audi' ]);
    $cars->add(4, 1.0, [ 'type' => 'Sedan', 'brand' => 'BMW' ]);
    $bands->add('cars', new SplitterBands($cars));

    expect($bands->for('cars')->value())->toBe(10.0);
});

test('throws exception for missing key', function () {
    (new Bands())->for('missing');
})->throws(RefractException::class, "Bands for 'missing' are not loaded");