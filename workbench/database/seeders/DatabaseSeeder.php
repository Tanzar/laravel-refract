<?php

namespace Workbench\Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Workbench\App\Models\Food;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Food::factory()->createMany([
            ['name' => 'Burger', 'category' => 'fast_food', 'price' => 13],
            ['name' => 'CheeseBurger', 'category' => 'fast_food', 'price' => 15],
            ['name' => 'Double Burger', 'category' => 'fast_food', 'price' => 15],
            ['name' => 'Apple', 'category' => 'Fruit', 'price' => 1.5],
            ['name' => 'Banana', 'category' => 'Fruit', 'price' => 2.5],
            ['name' => 'Pineapple', 'category' => 'Fruit', 'price' => 4],
            ['name' => 'Carrot', 'category' => 'Vegetable', 'price' => 2.5],
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

        DB::table('refract_bands_params')->insert([
            [ 'splitter_id' => 1, 'band_index' => 1, 'param_id' => 1, 'key_name' => 'category' ],
            [ 'splitter_id' => 1, 'band_index' => 1, 'param_id' => 4, 'key_name' => 'price' ],
            [ 'splitter_id' => 1, 'band_index' => 2, 'param_id' => 1, 'key_name' => 'category' ],
            [ 'splitter_id' => 1, 'band_index' => 2, 'param_id' => 5, 'key_name' => 'price' ],
            [ 'splitter_id' => 1, 'band_index' => 3, 'param_id' => 2, 'key_name' => 'category' ],
            [ 'splitter_id' => 1, 'band_index' => 3, 'param_id' => 6, 'key_name' => 'price' ],
            [ 'splitter_id' => 1, 'band_index' => 4, 'param_id' => 2, 'key_name' => 'category' ],
            [ 'splitter_id' => 1, 'band_index' => 4, 'param_id' => 7, 'key_name' => 'price' ],
            [ 'splitter_id' => 1, 'band_index' => 5, 'param_id' => 2, 'key_name' => 'category' ],
            [ 'splitter_id' => 1, 'band_index' => 5, 'param_id' => 8, 'key_name' => 'price' ],
            [ 'splitter_id' => 1, 'band_index' => 6, 'param_id' => 3, 'key_name' => 'category' ],
            [ 'splitter_id' => 1, 'band_index' => 6, 'param_id' => 7, 'key_name' => 'price' ]
        ]);

    }
}
