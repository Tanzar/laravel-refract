<?php

use Tanzar\Refract\Exceptions\RefractException;
use Tanzar\Refract\Support\RefractFactory;
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