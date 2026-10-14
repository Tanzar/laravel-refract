<?php

use Tanzar\Refract\Exceptions\RefractException;
use Tanzar\Refract\Support\Bands\Bands;
use Tanzar\Refract\Support\Bands\SplitterBands;
use Tanzar\Refract\Support\Bands\Tree\BandsBranch;
use Workbench\App\Splitters\TotalFoodsSplitter;

test('correctly loads multiple different bands', function() {
    $bands = new Bands();

    $splitter = new TotalFoodsSplitter();

    $cars = new BandsBranch(0, ['type', 'brand']);
    $cars->add(1, 3.0, [ 'type' => 'SUV', 'brand' => 'BMW' ]);
    $cars->add(2, 2.0, [ 'type' => 'SUV', 'brand' => 'Audi' ]);
    $cars->add(3, 4.0, [ 'type' => 'Kombi', 'brand' => 'Audi' ]);
    $cars->add(4, 1.0, [ 'type' => 'Sedan', 'brand' => 'BMW' ]);
    $bands->add('cars', $splitter, new SplitterBands($cars));

    expect($bands->for('cars')->value())->toBe(10.0);
    expect($bands->pullRequestedBands())->toBe([ 1 => [1, 2, 3, 4]]);
});

test('throws exception for missing key', function () {
    (new Bands())->for('missing');
})->throws(RefractException::class, "Bands for 'missing' are not loaded");