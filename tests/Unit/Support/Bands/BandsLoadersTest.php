<?php

use Tanzar\Refract\Exceptions\RefractException;
use Tanzar\Refract\Support\Bands\Loader\BandsLoaders;
use Tanzar\Refract\Support\Filters\Filters;
use Workbench\App\Splitters\TotalFoodsSplitter;
use Workbench\Database\Seeders\DatabaseSeeder;

test('correctly loads multiple different bands', function() {
    $this->seed(DatabaseSeeder::class);

    $filters = new Filters();

    $loaders = new BandsLoaders($filters);

    $loaders->loader('first', TotalFoodsSplitter::class)
        ->where('category', 'fast_food')
        ->whereBetween('price', 14, 100);

    $loaders->loader('second', TotalFoodsSplitter::class)
        ->where('category', 'Fruit');

    $bands = $loaders->load();

    expect($bands->for('first')->value())->toBe(2.0);
    expect($bands->for('second')->value())->toBe(3.0);
});

test('throws exception on empty loaders', function () {

    $filters = new Filters();

    $loaders = new BandsLoaders($filters);

    $loaders->load();

})->throws(RefractException::class, 'No loaders were set');