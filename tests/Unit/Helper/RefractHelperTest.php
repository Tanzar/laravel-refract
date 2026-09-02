<?php

use Tanzar\Refract\Exceptions\RefractException;
use Tanzar\Refract\Helpers\RefractHelper;
use Workbench\App\Splitters\TotalFoodsSplitter;

test('RefractHelper precision', function() {
    config()->set('refract.precision', 10);

    $precision = RefractHelper::precision();
    expect($precision)->toBe(10);
});

test('creates new splitter from class name', function() {
    $splitter = RefractHelper::splitter(TotalFoodsSplitter::class);
    expect($splitter)->toBeInstanceOf(TotalFoodsSplitter::class);
});

test('creates new splitter from alias', function() {
    config()->set('refract.splitters.aliases', [
        'test_splitter' => TotalFoodsSplitter::class,
    ]);

    $splitter = RefractHelper::splitter('test_splitter');
    expect($splitter)->toBeInstanceOf(TotalFoodsSplitter::class);
}); 

test('throws exception for invalid splitter class', function() {
    RefractHelper::splitter('InvalidSplitterClass');
})->throws(RefractException::class, 'Class InvalidSplitterClass is not a valid Splitter');