<?php

use Tanzar\Refract\Exceptions\RefractException;
use Tanzar\Refract\Support\Bands\Loader\BandsLoader;
use Tanzar\Refract\Support\Bands\SplitterBands;
use Tanzar\Refract\Support\Filters\Filters;
use Workbench\App\Splitters\TotalFoodsSplitter;
use Workbench\Database\Seeders\DatabaseSeeder;

test('loads and finds correct bands', function () {
    $this->seed(DatabaseSeeder::class);

    $filters = new Filters();
    $filters->float('price', 5.0);
    
    $loader = new BandsLoader(TotalFoodsSplitter::class, $filters);
    $loader->where('category', 'Fruit')
        ->whereBetween('price', 2.0, Filters::ref('price'));

    $bands = $loader->load();
    expect($bands->find()->value())->toBe(2.0);
    /** @var SplitterBands $bands */
   expect($bands->pullRequestedBands())->toBe([4, 5]);

    $second = new BandsLoader(TotalFoodsSplitter::class, $filters);
    $second->whereIn('category', [ 'Fruit', 'Vegetable' ]);
    $secondBands = $second->load();

    expect($secondBands->find()->value())->toBe(4.0);
    /** @var SplitterBands $secondBands */
    expect($secondBands->pullRequestedBands())->toBe([3, 4, 5, 6]);
});

test('loads empty when not found bands', function() {
    $this->seed(DatabaseSeeder::class);

    $filters = new Filters();
    $filters->float('price', 5.0);
    
    $loader = new BandsLoader(TotalFoodsSplitter::class, $filters);
    $loader->where('category', 'Candy');

    $bands = $loader->load();
    expect($bands->find()->value())->toBe(0.0);
    /** @var SplitterBands $bands */
   expect($bands->pullRequestedBands())->toBe([]);
});

test("don't load without conditions set", function () {

    $filters = new Filters();
    $filters->float('price', 5.0);
    
    $loader = new BandsLoader(TotalFoodsSplitter::class, $filters);
    $loader->load();

})->throws(RefractException::class, 'Loader must have at least 1 condition set');

test("throws exception on wrong key", function () {

    $filters = new Filters();
    $filters->float('price', 5.0);
    
    $loader = new BandsLoader(TotalFoodsSplitter::class, $filters);
    $loader->where('category', 'Fruit')->where('inBox', true);

})->throws(RefractException::class, "Key 'inBox' is not defined in splitter: " . TotalFoodsSplitter::class);

