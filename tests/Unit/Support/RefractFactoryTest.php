<?php

use Tanzar\Refract\Exceptions\RefractException;
use Tanzar\Refract\Support\RefractFactory;
use Workbench\App\Lens\TotalFoodsLens;
use Workbench\App\Splitters\TotalFoodsSplitter;

test('creates new splitter from class name', function() {
    $splitter = RefractFactory::splitter(TotalFoodsSplitter::class);
    expect($splitter)->toBeInstanceOf(TotalFoodsSplitter::class);
});

test('creates new splitter from alias', function() {
    config()->set('refract.splitters.aliases', [
        'test_splitter' => TotalFoodsSplitter::class,
    ]);

    $splitter = RefractFactory::splitter('test_splitter');
    expect($splitter)->toBeInstanceOf(TotalFoodsSplitter::class);
}); 

test('throws exception for invalid splitter class', function() {
    RefractFactory::splitter('InvalidSplitterClass');
})->throws(RefractException::class, 'Class InvalidSplitterClass is not a valid Splitter');

test('creates new lens from class name', function() {
    $splitter = RefractFactory::lens(TotalFoodsLens::class);
    expect($splitter)->toBeInstanceOf(TotalFoodsLens::class);
});

test('creates new lens from alias', function() {
    config()->set('refract.lens.aliases', [
        'test_lens' => TotalFoodsLens::class,
    ]);

    $splitter = RefractFactory::lens('test_lens');
    expect($splitter)->toBeInstanceOf(TotalFoodsLens::class);
}); 

test('throws exception for invalid lens class', function() {
    RefractFactory::lens('InvalidLensClass');
})->throws(RefractException::class, 'Class InvalidLensClass is not a valid Lens');