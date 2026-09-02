<?php

use Illuminate\Support\Facades\DB;
use Mockery;
use Tanzar\Refract\Services\BandsDeltaCalculator;
use Tanzar\Refract\Services\BandsRepository;
use Workbench\App\Splitters\TotalFoodsSplitter;

test('correctly updates database', function() {

    $splitter = new TotalFoodsSplitter();

    DB::table('refract_bands')->insert([
        [ 'splitter_id' => 1, 'band_index' => 1, 'signature_hash' => 'hash_a', 'current_value' => 5 ],
        [ 'splitter_id' => 1, 'band_index' => 2, 'signature_hash' => 'hash_b', 'current_value' => 15 ],
        [ 'splitter_id' => 1, 'band_index' => 3, 'signature_hash' => 'hash_c', 'current_value' => 5 ],
    ]);

    DB::table('refract_model_bands')->insert([
        [ 'splitter_id' => 1, 'model_id' => 1, 'band_index' => 2, 'current_value' => 5 ],
        [ 'splitter_id' => 1, 'model_id' => 2, 'band_index' => 2, 'current_value' => 5 ],
        [ 'splitter_id' => 1, 'model_id' => 3, 'band_index' => 2, 'current_value' => 5 ],
        [ 'splitter_id' => 1, 'model_id' => 4, 'band_index' => 1, 'current_value' => 5 ],
        [ 'splitter_id' => 1, 'model_id' => 5, 'band_index' => 3, 'current_value' => 5 ],
    ]);
    
    $calculator = Mockery::mock(BandsDeltaCalculator::class);

    $calculator->shouldReceive('getDeltas')->andReturn([ 1 => 5, 2 => -5, 3 => -5 ]);
    $calculator->shouldReceive('getPivotUpdates')
        ->andReturn([[ 'model_id' => 1, 'band_index' => 1, 'value' => 5 ]]);
    $calculator->shouldReceive('getPivotDeletes')->andReturn([5]);
    $calculator->shouldReceive('getAffectedBandIndices')->andReturn([1, 2, 3]);

    $repository = new BandsRepository($splitter);

    $repository->persist($calculator);

    $this->assertDatabaseCount('refract_bands', 3);
    $this->assertDatabaseHas('refract_bands', [ 'splitter_id' => 1, 'band_index' => 1, 'current_value' => 10 ]);
    $this->assertDatabaseHas('refract_bands', [ 'splitter_id' => 1, 'band_index' => 2, 'current_value' => 10 ]);
    $this->assertDatabaseHas('refract_bands', [ 'splitter_id' => 1, 'band_index' => 3, 'current_value' => 0 ]);

    $this->assertDatabaseCount('refract_model_bands', 4);
    $this->assertDatabaseHas('refract_model_bands', [ 'splitter_id' => 1, 'model_id' => 1, 'band_index' => 1, 'current_value' => 5 ]);
    $this->assertDatabaseMissing('refract_model_bands', [ 'model_id' => 5 ]);
});

test('no delta and no pivot updates or deletes', function() {

    $splitter = new TotalFoodsSplitter();

    $calculator = Mockery::mock(BandsDeltaCalculator::class);

    $calculator->shouldReceive('getDeltas')->andReturn([]);
    $calculator->shouldReceive('getPivotUpdates')->andReturn([]);
    $calculator->shouldReceive('getPivotDeletes')->andReturn([]);
    $calculator->shouldReceive('getAffectedBandIndices')->andReturn([]);

    $repository = new BandsRepository($splitter);

    $repository->persist($calculator);

    $this->assertDatabaseCount('refract_bands', 0);
    $this->assertDatabaseCount('refract_model_bands', 0);
});