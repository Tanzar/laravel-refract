<?php

namespace Workbench\Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tanzar\Refract\Enums\LensStates;
use Workbench\App\Lens\TotalFoodsLens;

class LensSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        new DatabaseSeeder()->run();

        DB::table('refract_cached_lens')->insert([
            'id' => 1,
            'lens_type' => TotalFoodsLens::class,
            'filters_hash' => '0adbfaebef06813ff5873bf1d2911fe4ce4fc947dbdf36beb9b3c3ebd4d68df2',
            'filters' => json_encode([ 'max' => [ 'value' => 15, 'type' => 'float' ] ]),
            'state' => LensStates::READY->value,
            'meta_data' => json_encode([]),
            'last_requested_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);

        DB::table('refract_lens_paths')->insert([
            [ 'cached_lens_id' => 1, 'path' => 'cheap', 'path_value' => json_encode([ 'value' => 1, 'options' => [] ]) ],
            [ 'cached_lens_id' => 1, 'path' => 'expensive', 'path_value' => json_encode([ 'value' => 2, 'options' => [] ]) ]
        ]);

        DB::table('refract_lens_bands')->insert([
            [ 'cached_lens_id' => 1, 'path' => 'cheap', 'splitter_id' => 1, 'band_index' => 1 ],
            [ 'cached_lens_id' => 1, 'path' => 'expensive', 'splitter_id' => 1, 'band_index' => 2 ],
        ]);
    }
}
