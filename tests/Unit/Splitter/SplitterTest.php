<?php

use Tanzar\Refract\Splitter\SplitterParams;
use Workbench\App\Models\Food;
use Workbench\App\Models\User;
use Workbench\App\Splitters\TotalFoodsSplitter;

test('Splitter dont make new records', function () {
    $splitter = new TotalFoodsSplitter();

    $this->assertDatabaseHas('refract_splitters', [
        'splitter_type' => TotalFoodsSplitter::class,
        'model_type' => Food::class,
        'encoded_params' => "category:string:general;price:float:0;",
    ]);

    $details = $splitter->id();

    expect($details)->toBe(1);

    $secondInstance = new TotalFoodsSplitter();

    expect($secondInstance->id())->toBe(1);

    $this->assertDatabaseCount('refract_splitters', 1);
});

test('TotalFoodsSplitter splits food model correctly', function () {
    $splitter = new TotalFoodsSplitter();

    $food = Food::factory()->create([
        'name' => 'Apple',
        'price' => 1.5,
        'category' => 'Fruit',
    ]);

    $params = $splitter->split($food);

    expect($params)->toBeInstanceOf(SplitterParams::class);
    expect($params->getValue('category'))->toBe('Fruit');
    expect($params->getValue('price'))->toBe(1.5);
});

test('Splitter returns null for wrong model', function () {
    $splitter = new TotalFoodsSplitter();

    $wrongModel = new User();

    $result = $splitter->split($wrongModel);

    expect($result)->toBeNull();
});

test('Splitter relations method', function () {
    $splitter = new TotalFoodsSplitter();

    $relations = $splitter->relations();

    expect($relations)->toBe([]);
});