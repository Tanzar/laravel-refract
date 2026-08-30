<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tanzar\Refract\Jobs\SplitterUpdateJob;
use Tanzar\Refract\Services\SplitterUpdate\SplitterProcessor;
use Workbench\App\Models\Food;
use Workbench\App\Splitters\TotalFoodsSplitter;

beforeEach(function() {
    Food::factory()->createMany([
        ['name' => 'Burger', 'category' => 'fast_food', 'price' => 13],
        ['name' => 'CheeseBurger', 'category' => 'fast_food', 'price' => 15],
        ['name' => 'Double Burger', 'category' => 'fast_food', 'price' => 15],
        ['name' => 'Apple', 'category' => 'Fruit', 'price' => 1.5],
        ['name' => 'Banana', 'category' => 'Fruit', 'price' => 2.5],
        ['name' => 'Pineapple', 'category' => 'Fruit', 'price' => 4],
        ['name' => 'Carrot', 'category' => 'Vegetable', 'price' => 2.5],
    ]);
});

test('SplitterUpdateJob fills empty tables', function () {
    Event::fake();

    $ids = Food::pluck('id')->all();

    $job = new SplitterUpdateJob(TotalFoodsSplitter::class, $ids);

    $job->handle(new SplitterProcessor());

    $this->assertDatabaseCount('refract_params', 8);

    $this->assertDatabaseHas('refract_params', [ 'type' => 'string', 'raw_value' => 'fast_food', 'string_value' => 'fast_food' ]);
    $this->assertDatabaseHas('refract_params', [ 'type' => 'string', 'raw_value' => 'Fruit', 'string_value' => 'Fruit' ]);
    $this->assertDatabaseHas('refract_params', [ 'type' => 'string', 'raw_value' => 'Vegetable', 'string_value' => 'Vegetable' ]);

    $this->assertDatabaseHas('refract_params', [ 'type' => 'float', 'raw_value' => '13', 'float_value' => 13 ]);
    $this->assertDatabaseHas('refract_params', [ 'type' => 'float', 'raw_value' => '15', 'float_value' => 15 ]);
    $this->assertDatabaseHas('refract_params', [ 'type' => 'float', 'raw_value' => '1.5', 'float_value' => 1.5 ]);
    $this->assertDatabaseHas('refract_params', [ 'type' => 'float', 'raw_value' => '2.5', 'float_value' => 2.5 ]);
    $this->assertDatabaseHas('refract_params', [ 'type' => 'float', 'raw_value' => '4', 'float_value' => 4 ]);

    $this->assertDatabaseCount('refract_splitters', 1);
    $this->assertDatabaseHas('refract_splitters', [
        'splitter_type' => 'Workbench\\App\\Splitters\\TotalFoodsSplitter',
        'model_type' => 'Workbench\\App\\Models\\Food',
        'bands_count' => 6,
        'encoded_params' => 'category:string:general;price:float:0;'
    ]);

    $this->assertDatabaseCount('refract_model_bands', 7);
    $this->assertDatabaseHas('refract_model_bands', [ 'model_id' => 1, 'splitter_id' => 1, 'band_index' => 1, 'current_value' => 1 ]);
    $this->assertDatabaseHas('refract_model_bands', [ 'model_id' => 2, 'splitter_id' => 1, 'band_index' => 2, 'current_value' => 1 ]);
    $this->assertDatabaseHas('refract_model_bands', [ 'model_id' => 3, 'splitter_id' => 1, 'band_index' => 2, 'current_value' => 1 ]);
    $this->assertDatabaseHas('refract_model_bands', [ 'model_id' => 4, 'splitter_id' => 1, 'band_index' => 3, 'current_value' => 1 ]);
    $this->assertDatabaseHas('refract_model_bands', [ 'model_id' => 5, 'splitter_id' => 1, 'band_index' => 4, 'current_value' => 1 ]);
    $this->assertDatabaseHas('refract_model_bands', [ 'model_id' => 6, 'splitter_id' => 1, 'band_index' => 5, 'current_value' => 1 ]);
    $this->assertDatabaseHas('refract_model_bands', [ 'model_id' => 7, 'splitter_id' => 1, 'band_index' => 6, 'current_value' => 1 ]);

    $this->assertDatabaseCount('refract_bands', 6);
    $this->assertDatabaseHas('refract_bands',
        [ 'splitter_id' => 1, 'band_index' => 1, 'signature_hash' => 'b7b5767b4352a9188b226f087b6ce02f', 'current_value' => 1 ]
    );
    $this->assertDatabaseHas('refract_bands',
        [ 'splitter_id' => 1, 'band_index' => 2, 'signature_hash' => '3e807161aeb8dcc3ade4c1a0f8190318', 'current_value' => 2 ]
    );
    $this->assertDatabaseHas('refract_bands',
        [ 'splitter_id' => 1, 'band_index' => 3, 'signature_hash' => 'a95f0d6cb1528579ddcc262b9fe5b777', 'current_value' => 1 ]
    );
    $this->assertDatabaseHas('refract_bands',
        [ 'splitter_id' => 1, 'band_index' => 4, 'signature_hash' => 'f1863d7c34e309a20ae1dc53cfc20b69', 'current_value' => 1 ]
    );
    $this->assertDatabaseHas('refract_bands',
        [ 'splitter_id' => 1, 'band_index' => 5, 'signature_hash' => '5a332475f3929209182f3788120f817a', 'current_value' => 1 ]
    );
    $this->assertDatabaseHas('refract_bands',
        [ 'splitter_id' => 1, 'band_index' => 6, 'signature_hash' => '739db051b7948ce761df7a8acfc9adb7', 'current_value' => 1 ]
    );

    $this->assertDatabaseCount('refract_bands_params', 12);
    $this->assertDatabaseHas('refract_bands_params',
        [ 'splitter_id' => 1, 'band_index' => 1, 'param_id' => 1, 'key_name' => 'category' ]
    );
    $this->assertDatabaseHas('refract_bands_params',
        [ 'splitter_id' => 1, 'band_index' => 1, 'param_id' => 2, 'key_name' => 'price' ]
    );
    $this->assertDatabaseHas('refract_bands_params',
        [ 'splitter_id' => 1, 'band_index' => 2, 'param_id' => 1, 'key_name' => 'category' ]
    );
    $this->assertDatabaseHas('refract_bands_params',
        [ 'splitter_id' => 1, 'band_index' => 2, 'param_id' => 3, 'key_name' => 'price' ]
    );
    $this->assertDatabaseHas('refract_bands_params',
        [ 'splitter_id' => 1, 'band_index' => 3, 'param_id' => 4, 'key_name' => 'category' ]
    );
    $this->assertDatabaseHas('refract_bands_params',
        [ 'splitter_id' => 1, 'band_index' => 3, 'param_id' => 5, 'key_name' => 'price' ]
    );
    $this->assertDatabaseHas('refract_bands_params',
        [ 'splitter_id' => 1, 'band_index' => 4, 'param_id' => 4, 'key_name' => 'category' ]
    );
    $this->assertDatabaseHas('refract_bands_params',
        [ 'splitter_id' => 1, 'band_index' => 4, 'param_id' => 6, 'key_name' => 'price' ]
    );
    $this->assertDatabaseHas('refract_bands_params',
        [ 'splitter_id' => 1, 'band_index' => 5, 'param_id' => 4, 'key_name' => 'category' ]
    );
    $this->assertDatabaseHas('refract_bands_params',
        [ 'splitter_id' => 1, 'band_index' => 5, 'param_id' => 7, 'key_name' => 'price' ]
    );
    $this->assertDatabaseHas('refract_bands_params',
        [ 'splitter_id' => 1, 'band_index' => 6, 'param_id' => 8, 'key_name' => 'category' ]
    );
    $this->assertDatabaseHas('refract_bands_params',
        [ 'splitter_id' => 1, 'band_index' => 6, 'param_id' => 6, 'key_name' => 'price' ]
    );

});

test('SplitterUpdateJob updates existing tables', function () {
    Event::fake();

    // === INITIALIZE DATABASE === //
    
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
        [ 'splitter_id' => 1, 'band_index' => 1, 'signature_hash' => 'b7b5767b4352a9188b226f087b6ce02f', 'current_value' => 1 ]
    );
    DB::table('refract_bands')->insert(
        [ 'splitter_id' => 1, 'band_index' => 2, 'signature_hash' => '3e807161aeb8dcc3ade4c1a0f8190318', 'current_value' => 2 ]
    );
    DB::table('refract_bands')->insert(
        [ 'splitter_id' => 1, 'band_index' => 3, 'signature_hash' => 'a95f0d6cb1528579ddcc262b9fe5b777', 'current_value' => 1 ]
    );
    DB::table('refract_bands')->insert(
        [ 'splitter_id' => 1, 'band_index' => 4, 'signature_hash' => 'f1863d7c34e309a20ae1dc53cfc20b69', 'current_value' => 1 ]
    );
    DB::table('refract_bands')->insert(
        [ 'splitter_id' => 1, 'band_index' => 5, 'signature_hash' => '5a332475f3929209182f3788120f817a', 'current_value' => 1 ]
    );
    DB::table('refract_bands')->insert(
        [ 'splitter_id' => 1, 'band_index' => 6, 'signature_hash' => '739db051b7948ce761df7a8acfc9adb7', 'current_value' => 1 ]
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

    // === UPDATE MODELS === //
    $foods = [
        [ 'id' => 1, 'category' => 'fast_food', 'price' => 15 ],
        [ 'id' => 3, 'category' => 'fast_food', 'price' => 17 ],
        [ 'id' => 6, 'category' => 'Vegetable', 'price' => 3 ],
        [ 'id' => 4, 'category' => 'Fruit', 'price' => 2.5 ],
    ];

    foreach ($foods as $food) {
        DB::table('food')
            ->where('id', $food['id'])
            ->update([
                'category' => $food['category'],
                'price' => $food['price']
            ]);
    }

    $ids = Food::pluck('id')->all();

    $job = new SplitterUpdateJob(TotalFoodsSplitter::class, $ids);

    $job->handle(new SplitterProcessor());

    $this->assertDatabaseCount('refract_params', 10);

    $this->assertDatabaseHas('refract_params', [ 'type' => 'float', 'float_value' => 17 ]);
    $this->assertDatabaseHas('refract_params', [ 'type' => 'float', 'float_value' => 3 ]);

    $this->assertDatabaseCount('refract_splitters', 1);
    $this->assertDatabaseHas('refract_splitters', [
        'splitter_type' => 'Workbench\\App\\Splitters\\TotalFoodsSplitter',
        'model_type' => 'Workbench\\App\\Models\\Food',
        'bands_count' => 8,
        'encoded_params' => 'category:string:general;price:float:0;'
    ]);

    $this->assertDatabaseCount('refract_model_bands', 7);
    $this->assertDatabaseHas('refract_model_bands', [ 'model_id' => 1, 'splitter_id' => 1, 'band_index' => 2, 'current_value' => 1 ]);
    $this->assertDatabaseHas('refract_model_bands', [ 'model_id' => 2, 'splitter_id' => 1, 'band_index' => 2, 'current_value' => 1 ]);
    $this->assertDatabaseHas('refract_model_bands', [ 'model_id' => 3, 'splitter_id' => 1, 'band_index' => 7, 'current_value' => 1 ]);
    $this->assertDatabaseHas('refract_model_bands', [ 'model_id' => 4, 'splitter_id' => 1, 'band_index' => 4, 'current_value' => 1 ]);
    $this->assertDatabaseHas('refract_model_bands', [ 'model_id' => 5, 'splitter_id' => 1, 'band_index' => 4, 'current_value' => 1 ]);
    $this->assertDatabaseHas('refract_model_bands', [ 'model_id' => 6, 'splitter_id' => 1, 'band_index' => 8, 'current_value' => 1 ]);
    $this->assertDatabaseHas('refract_model_bands', [ 'model_id' => 7, 'splitter_id' => 1, 'band_index' => 6, 'current_value' => 1 ]);

    $this->assertDatabaseCount('refract_bands', 8);
    $this->assertDatabaseHas('refract_bands',
        [ 'splitter_id' => 1, 'band_index' => 1, 'signature_hash' => 'b7b5767b4352a9188b226f087b6ce02f', 'current_value' => 0 ]
    );
    $this->assertDatabaseHas('refract_bands',
        [ 'splitter_id' => 1, 'band_index' => 2, 'signature_hash' => '3e807161aeb8dcc3ade4c1a0f8190318', 'current_value' => 2 ]
    );
    $this->assertDatabaseHas('refract_bands',
        [ 'splitter_id' => 1, 'band_index' => 3, 'signature_hash' => 'a95f0d6cb1528579ddcc262b9fe5b777', 'current_value' => 0 ]
    );
    $this->assertDatabaseHas('refract_bands',
        [ 'splitter_id' => 1, 'band_index' => 4, 'signature_hash' => 'f1863d7c34e309a20ae1dc53cfc20b69', 'current_value' => 2 ]
    );
    $this->assertDatabaseHas('refract_bands',
        [ 'splitter_id' => 1, 'band_index' => 5, 'signature_hash' => '5a332475f3929209182f3788120f817a', 'current_value' => 0 ]
    );
    $this->assertDatabaseHas('refract_bands',
        [ 'splitter_id' => 1, 'band_index' => 6, 'signature_hash' => '739db051b7948ce761df7a8acfc9adb7', 'current_value' => 1 ]
    );
    $this->assertDatabaseHas('refract_bands',
        [ 'splitter_id' => 1, 'band_index' => 7, 'signature_hash' => '913a83adc792544d676c8ab7ea824e8b', 'current_value' => 1 ]
    );
    $this->assertDatabaseHas('refract_bands',
        [ 'splitter_id' => 1, 'band_index' => 8, 'signature_hash' => '4ce0fa6913ca395fd465246482cd4931', 'current_value' => 1 ]
    );

    $this->assertDatabaseCount('refract_bands_params', 16);
    $this->assertDatabaseHas('refract_bands_params',
        [ 'splitter_id' => 1, 'band_index' => 7, 'param_id' => 1, 'key_name' => 'category' ]
    );
    $this->assertDatabaseHas('refract_bands_params',
        [ 'splitter_id' => 1, 'band_index' => 7, 'param_id' => 9, 'key_name' => 'price' ]
    );
    $this->assertDatabaseHas('refract_bands_params',
        [ 'splitter_id' => 1, 'band_index' => 8, 'param_id' => 3, 'key_name' => 'category' ]
    );
    $this->assertDatabaseHas('refract_bands_params',
        [ 'splitter_id' => 1, 'band_index' => 8, 'param_id' => 10, 'key_name' => 'price' ]
    );

});