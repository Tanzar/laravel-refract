<?php

use Illuminate\Support\Facades\DB;
use Mockery;
use Tanzar\Refract\Splitter\RequiredParams;
use Tanzar\Refract\Splitter\SplitterParams;
use Tanzar\Refract\Support\Splitters\DeltaCalculator;
use Workbench\App\Models\Food;

beforeEach(function () {
    config()->set('refract.precision', 4);

    Food::insert([
        [ 'id' => 1, 'name' => 'Burger', 'category' => 'fast_food', 'price' => 13 ],
        [ 'id' => 2, 'name' => 'CheeseBurger', 'category' => 'fast_food', 'price' => 15 ],
        [ 'id' => 3, 'name' => 'Double Burger', 'category' => 'fast_food', 'price' => 15 ],
        [ 'id' => 4, 'name' => 'Apple', 'category' => 'Fruit', 'price' => 1.5 ],
        [ 'id' => 5, 'name' => 'Banana', 'category' => 'Fruit', 'price' => 2.5 ],
        [ 'id' => 6, 'name' => 'Pineapple', 'category' => 'Fruit', 'price' => 4 ],
        [ 'id' => 7, 'name' => 'Carrot', 'category' => 'Vegetable', 'price' => 2.5 ],
    ]);

      
    DB::table('refract_params')->insert([ 'type' => 'string', 'raw_value' => 'fast_food', 'string_value' => 'fast_food' ]);
    DB::table('refract_params')->insert([ 'type' => 'string', 'raw_value' => 'Fruit',  'string_value' => 'Fruit' ]);
    DB::table('refract_params')->insert([ 'type' => 'string', 'raw_value' => 'Vegetable',  'string_value' => 'Vegetable' ]);

    DB::table('refract_params')->insert([ 'type' => 'float', 'raw_value' => '13',  'float_value' => 13 ]);
    DB::table('refract_params')->insert([ 'type' => 'float', 'raw_value' => '15',  'float_value' => 15 ]);
    DB::table('refract_params')->insert([ 'type' => 'float', 'raw_value' => '1.5',  'float_value' => 1.5 ]);
    DB::table('refract_params')->insert([ 'type' => 'float', 'raw_value' => '2.5',  'float_value' => 2.5 ]);
    DB::table('refract_params')->insert([ 'type' => 'float', 'raw_value' => '4',  'float_value' => 4 ]);

    DB::table('refract_splitters')->insert([
        'splitter_type' => 'Workbench\\App\\Splitters\\TotalFoodsSplitter',
        'model_type' => 'Workbench\\App\\Models\\Food',
        'bands_count' => 6,
        'encoded_params' => 'category:string:general;price:float:0;'
    ]);

    DB::table('refract_model_bands')->insert([ 'model_id' => 1, 'splitter_id' => 1, 'band_index' => 1, 'current_value' => 1 ]);
    DB::table('refract_model_bands')->insert([ 'model_id' => 2, 'splitter_id' => 1, 'band_index' => 2, 'current_value' => 1 ]);
    DB::table('refract_model_bands')->insert([ 'model_id' => 3, 'splitter_id' => 1, 'band_index' => 2, 'current_value' => 1 ]);
    DB::table('refract_model_bands')->insert([ 'model_id' => 4, 'splitter_id' => 1, 'band_index' => 3, 'current_value' => 1 ]);
    DB::table('refract_model_bands')->insert([ 'model_id' => 5, 'splitter_id' => 1, 'band_index' => 4, 'current_value' => 1 ]);
    DB::table('refract_model_bands')->insert([ 'model_id' => 6, 'splitter_id' => 1, 'band_index' => 5, 'current_value' => 1 ]);
    DB::table('refract_model_bands')->insert([ 'model_id' => 7, 'splitter_id' => 1, 'band_index' => 6, 'current_value' => 1 ]);

    DB::table('refract_bands')->insert(
        [
            'splitter_id' => 1,
            'band_index' => 1,
            'signature_hash' => '25aa13dc7254835216714fcfcbb35e706993b35ad1e1a3def9cceeccd5479c77',
            'current_value' => 1
        ]
    );
    DB::table('refract_bands')->insert(
        [
            'splitter_id' => 1,
            'band_index' => 2,
            'signature_hash' => 'bf37081ce9f5e438ae0c42973fe429e46f037801b7733e2b094df9d107cb834b',
            'current_value' => 2
        ]
    );
    DB::table('refract_bands')->insert(
        [
            'splitter_id' => 1,
            'band_index' => 3,
            'signature_hash' => '13e024d041a7faf48396bc5f490ac55ed114651e0cf60dddcccf500899b00400',
            'current_value' => 1
        ]
    );
    DB::table('refract_bands')->insert(
        [
            'splitter_id' => 1,
            'band_index' => 4,
            'signature_hash' => 'c839fc8afe9fd34e45957316e91ae8df86688454cd7da97ae4f96458499ed8f9',
            'current_value' => 1
        ]
    );
    DB::table('refract_bands')->insert(
        [
            'splitter_id' => 1,
            'band_index' => 5,
            'signature_hash' => 'cfee4b3df4bfa094c75e7e388346a193068ef625023129549d76e014d1473558',
            'current_value' => 1
        ]
    );
    DB::table('refract_bands')->insert(
        [
            'splitter_id' => 1,
            'band_index' => 6,
            'signature_hash' => '1362112153d641c66b2bb867f479e0f84b9509d545e8af072be2a668d05175ca',
            'current_value' => 1
        ]
    );

    DB::table('refract_bands_params')->insert(
        [ 'splitter_id' => 1, 'band_index' => 1, 'param_id' => 1, 'key_name' => 'category' ]
    );
    DB::table('refract_bands_params')->insert(
        [ 'splitter_id' => 1, 'band_index' => 1, 'param_id' => 2, 'key_name' => 'price' ]
    );
    DB::table('refract_bands_params')->insert(
        [ 'splitter_id' => 1, 'band_index' => 2, 'param_id' => 1, 'key_name' => 'category' ]
    );
    DB::table('refract_bands_params')->insert(
        [ 'splitter_id' => 1, 'band_index' => 2, 'param_id' => 3, 'key_name' => 'price' ]
    );
    DB::table('refract_bands_params')->insert(
        [ 'splitter_id' => 1, 'band_index' => 3, 'param_id' => 4, 'key_name' => 'category' ]
    );
    DB::table('refract_bands_params')->insert(
        [ 'splitter_id' => 1, 'band_index' => 3, 'param_id' => 5, 'key_name' => 'price' ]
    );
    DB::table('refract_bands_params')->insert(
        [ 'splitter_id' => 1, 'band_index' => 4, 'param_id' => 4, 'key_name' => 'category' ]
    );
    DB::table('refract_bands_params')->insert(
        [ 'splitter_id' => 1, 'band_index' => 4, 'param_id' => 6, 'key_name' => 'price' ]
    );
    DB::table('refract_bands_params')->insert(
        [ 'splitter_id' => 1, 'band_index' => 5, 'param_id' => 4, 'key_name' => 'category' ]
    );
    DB::table('refract_bands_params')->insert(
        [ 'splitter_id' => 1, 'band_index' => 5, 'param_id' => 7, 'key_name' => 'price' ]
    );
    DB::table('refract_bands_params')->insert(
        [ 'splitter_id' => 1, 'band_index' => 6, 'param_id' => 8, 'key_name' => 'category' ]
    );
    DB::table('refract_bands_params')->insert(
        [ 'splitter_id' => 1, 'band_index' => 6, 'param_id' => 6, 'key_name' => 'price' ]
    );
});

test('returns empty state when no models are analyzed', function () {
    $calculator = new DeltaCalculator(1, []);

    $calculator->calculate([]);

    expect($calculator->hasChanges())->toBeFalse();
});

test('adds new model to new band', function () {
    $calculator = new DeltaCalculator(1, [8]);

    $keys = new RequiredParams()
        ->string('category', 'general')
        ->float('price', 0.0);

    $param = new SplitterParams($keys, 1.0, 8)->string('category', 'fast_food')->float('price', 13);

    $calculator->analyze($param);
    $calculator->calculate(
        [ '25aa13dc7254835216714fcfcbb35e706993b35ad1e1a3def9cceeccd5479c77' => 1 ]
    );

    $deltas = $calculator->getDeltas();
    $pivots = $calculator->getPivots();

    expect($calculator->hasChanges())->toBeTrue();
    expect($deltas->toArray())->toBe([1 => 1.0]);
    expect($deltas->affectedBands())->toBe([1]);
    expect($pivots->toArray())->toBe([
        'updates' => [ ['model_id' => 8, 'band_index' => 1, 'value' => 1.0] ],
        'deletes' => []
    ]);
});

test('ignores model update when band index and value remain unchanged', function () {
    $calculator = new DeltaCalculator(1, [1]);

    $keys = new RequiredParams()
        ->string('category', 'general')
        ->float('price', 0.0);

    $param = new SplitterParams($keys, 1.0, 1)->string('category', 'fast_food')->float('price', 13);

    $calculator->analyze($param);
    $calculator->calculate(
        [ '25aa13dc7254835216714fcfcbb35e706993b35ad1e1a3def9cceeccd5479c77' => 1 ]
    );

    $deltas = $calculator->getDeltas();
    $pivots = $calculator->getPivots();

    expect($calculator->hasChanges())->toBeFalse();
    expect($deltas->toArray())->toBeEmpty();
    expect($deltas->affectedBands())->toBeEmpty();
    expect($pivots->toArray())->toBe([ 'updates' => [], 'deletes' => [] ]);
});

test('calculates value delta when model remains in the same band', function () {
    $calculator = new DeltaCalculator(1, [1]);

    $keys = new RequiredParams()
        ->string('category', 'general')
        ->float('price', 0.0);

    $param = new SplitterParams($keys, 2.0, 1)->string('category', 'fast_food')->float('price', 13);

    $calculator->analyze($param);
    $calculator->calculate(
        [ '25aa13dc7254835216714fcfcbb35e706993b35ad1e1a3def9cceeccd5479c77' => 1 ]
    );

    $deltas = $calculator->getDeltas();
    $pivots = $calculator->getPivots();

    expect($calculator->hasChanges())->toBeTrue();
    expect($deltas->toArray())->toBe([1 => 1.0]);
    expect($deltas->affectedBands())->toBe([1]);
    expect($pivots->toArray())->toBe([
        'updates' => [ [ 'model_id' => 1, 'band_index' => 1, 'value' => 2.0 ] ],
        'deletes' => []
    ]);
});

test('shifts value between bands when model changes band', function () {
    $calculator = new DeltaCalculator(1, [1]);

    $keys = new RequiredParams()
        ->string('category', 'general')
        ->float('price', 0.0);

    $param = new SplitterParams($keys, 1.0, 1)->string('category', 'fast_food')->float('price', 15);

    $calculator->analyze($param);
    $calculator->calculate([
        '25aa13dc7254835216714fcfcbb35e706993b35ad1e1a3def9cceeccd5479c77' => 1,
        'bf37081ce9f5e438ae0c42973fe429e46f037801b7733e2b094df9d107cb834b' => 2
    ]);
    
    $deltas = $calculator->getDeltas();
    $pivots = $calculator->getPivots();

    expect($calculator->hasChanges())->toBeTrue();
    expect($deltas->toArray())->toBe([ 1 => -1.0, 2 => 1.0 ]);
    expect($deltas->affectedBands())->toBe([ 1, 2 ]);
    expect($pivots->toArray())->toBe([
        'updates' => [ ['model_id' => 1, 'band_index' => 2, 'value' => 1.0 ] ],
        'deletes' => []
    ]);
});

test('handles missing model and marks it for pivot delete while subtracting delta', function () {
    DB::table('refract_bands')
        ->where('splitter_id', 1)
        ->where('band_index', 1)
        ->update(['current_value' => 2]);

    DB::table('refract_model_bands')->insert([ 'model_id' => 99, 'splitter_id' => 1, 'band_index' => 1, 'current_value' => 1 ]);

    $calculator = new DeltaCalculator(1, [99, 100]);
     
    $calculator->calculate([]);

    $deltas = $calculator->getDeltas();
    $pivots = $calculator->getPivots();

    expect($calculator->hasChanges())->toBeTrue();
    expect($deltas->toArray())->toBe([ 1 => -1.0 ]);
    expect($pivots->toArray())->toBe([
        'updates' => [],
        'deletes' => [ 99 ]
    ]);
});

test('aggregates multiple model changes correctly across shared bands', function () {
    $calculator = new DeltaCalculator(1, [1, 2]);

    $keys = new RequiredParams()
        ->string('category', 'general')
        ->float('price', 0.0);

    $calculator->analyze(new SplitterParams($keys, 2.0, 1)->string('category', 'fast_food')->float('price', 15));
    $calculator->analyze(new SplitterParams($keys, 2.0, 2)->string('category', 'fast_food')->float('price', 13));
    $calculator->calculate([
        '25aa13dc7254835216714fcfcbb35e706993b35ad1e1a3def9cceeccd5479c77' => 1,
        'bf37081ce9f5e438ae0c42973fe429e46f037801b7733e2b094df9d107cb834b' => 2
    ]);
    
    $deltas = $calculator->getDeltas();
    $pivots = $calculator->getPivots();

    expect($calculator->hasChanges())->toBeTrue();
    expect($deltas->toArray())->toBe([ 1 => 1.0, 2 => 1.0 ]);
    expect($deltas->affectedBands())->toBe([ 1, 2 ]);
    expect($pivots->toArray())->toBe([
        'updates' => [
            ['model_id' => 1, 'band_index' => 2, 'value' => 2.0],
            ['model_id' => 2, 'band_index' => 1, 'value' => 2.0]
        ],
        'deletes' => []
    ]);
});