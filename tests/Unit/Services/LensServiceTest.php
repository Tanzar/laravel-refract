<?php

use Mockery;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Sleep;
use Tanzar\Refract\Enums\LensStates;
use Tanzar\Refract\Events\RefractLensUpdated;
use Tanzar\Refract\Exceptions\RefractException;
use Tanzar\Refract\Models\CachedLens;
use Tanzar\Refract\Services\LensService;
use Tanzar\Refract\Support\Filters\Filters;
use Tanzar\Refract\Support\Lens\LensSynchronizer;
use Workbench\App\Lens\TotalFoodsLens;
use Workbench\Database\Seeders\DatabaseSeeder;
use Workbench\Database\Seeders\LensSeeder;

test('get creates new entry', function() {
    $this->seed(DatabaseSeeder::class);

    $filters = new Filters();
    $filters->float('max', 15.0);
    $service = new LensService();
    $result = $service->get(TotalFoodsLens::class, $filters);

    expect($result)->toBe([
        'lens' => [ 'cacheId' => 1, ],
        'meta' => [],
        'nodes' => [
            'cheap' => [ 'value' => 1.0, 'options' => [] ],
            'expensive' => [ 'value' => 2.0, 'options' => [] ],
        ]
    ]);

    $this->assertDatabaseHas('refract_cached_lens',
        [
            'id' => 1,
            'lens_type' => TotalFoodsLens::class,
            'filters_hash' => '0adbfaebef06813ff5873bf1d2911fe4ce4fc947dbdf36beb9b3c3ebd4d68df2',
            'filters' => json_encode([ 'max' => [ 'value' => 15, 'type' => 'float' ] ]),
            'state' => LensStates::READY->value,
            'meta_data' => json_encode([]),
            'last_requested_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);

    $this->assertDatabaseHas('refract_lens_paths',
        [ 'cached_lens_id' => 1, 'path' => 'cheap', 'path_value' => json_encode([ 'value' => 1, 'options' => [] ]) ]);
    
    $this->assertDatabaseHas('refract_lens_paths',
        [ 'cached_lens_id' => 1, 'path' => 'expensive', 'path_value' => json_encode([ 'value' => 2, 'options' => [] ]) ]);

    $this->assertDatabaseHas('refract_lens_bands',
        [ 'cached_lens_id' => 1, 'path' => 'cheap', 'splitter_id' => 1, 'band_index' => 1 ]);
    $this->assertDatabaseHas('refract_lens_bands',
        [ 'cached_lens_id' => 1, 'path' => 'expensive', 'splitter_id' => 1, 'band_index' => 2 ]);
});

test('get retrieves from cache', function () {
    $this->seed(LensSeeder::class);

    $filters = new Filters();
    $filters->float('max', 15.0);
    $service = new LensService();
    $result = $service->get(TotalFoodsLens::class, $filters);

    expect($result)->toBe([
        'lens' => [ 'cacheId' => 1, ],
        'meta' => [],
        'nodes' => [
            'cheap' => [ 'value' => 1, 'options' => [] ],
            'expensive' => [ 'value' => 2, 'options' => [] ],
        ]
    ]);

    $this->assertDatabaseCount('refract_cached_lens', 1);

    $this->assertDatabaseCount('refract_lens_paths', 2);

    $this->assertDatabaseCount('refract_lens_bands', 2);

});

test('get cancels saving into database on any error', function () {
    $this->seed(DatabaseSeeder::class);
    
    $synchronizerMock = Mockery::mock(LensSynchronizer::class);
    
    $synchronizerMock->shouldReceive('sync')
        ->once()
        ->andThrow(new Exception('Testowy błąd'));

    $this->app->bind(LensSynchronizer::class, function () use ($synchronizerMock) {
        return $synchronizerMock;
    });

    $filters = new Filters();
    $filters->float('max', 15.0);
    $service = new LensService();
    $message = '';
    try {
        $result = $service->get(TotalFoodsLens::class, $filters);
    }
    catch (Exception $e) {
        $message = $e->getMessage();
    }
    expect($message)->toBe('Testowy błąd');
    $this->assertDatabaseEmpty('refract_cached_lens');
});

test('get retrieves from cache, waits, then makes for self', function () {    
    $this->seed(LensSeeder::class);
    
    Sleep::fake();

    CachedLens::query()
        ->where('id', 1)
        ->update([
            'state' => LensStates::GENERATING
        ]);

    $filters = new Filters();
    $filters->float('max', 15.0);
    $service = new LensService();
    $result = $service->get(TotalFoodsLens::class, $filters);

    expect($result)->toBe([
        'lens' => [ 'cacheId' => 1, ],
        'meta' => [],
        'nodes' => [
            'cheap' => [ 'value' => 1.0, 'options' => [] ],
            'expensive' => [ 'value' => 2.0, 'options' => [] ],
        ]
    ]);

    $this->assertDatabaseCount('refract_cached_lens', 1);

    $this->assertDatabaseCount('refract_lens_paths', 2);

    $this->assertDatabaseCount('refract_lens_bands', 2);

});



test('updates cache tables correctly', function () {
    Event::fake();

    $this->seed(LensSeeder::class);

    DB::table('refract_lens_paths')->insert([
        [ 'cached_lens_id' => 1, 'path' => 'rich', 'path_value' => json_encode([ 'value' => 13, 'options' => [] ]) ]
    ]);
    DB::table('refract_lens_paths')
        ->where('path', 'cheap')
        ->where('cached_lens_id', 1)
        ->update([
            'path_value' => json_encode([ 'value' => 0, 'options' => [] ])
        ]);

    DB::table('refract_lens_bands')
        ->where('path', 'cheap')
        ->where('cached_lens_id', 1)
        ->delete();

    DB::table('refract_lens_bands')->insert([
        [ 'cached_lens_id' => 1, 'path' => 'cheap', 'splitter_id' => 1, 'band_index' => 19 ],
        [ 'cached_lens_id' => 1, 'path' => 'rich', 'splitter_id' => 1, 'band_index' => 579 ],
    ]);
    
    $service = new LensService();
    $service->update(1);

    Event::assertDispatched(RefractLensUpdated::class, function (RefractLensUpdated $event) {
        return $event->cachedLensId === 1 &&
            $event->state === [
                'lens' => [ 'cacheId' => 1, ],
                'meta' => [],
                'nodes' => [
                    'cheap' => [ 'value' => 1.0, 'options' => [] ],
                    'expensive' => [ 'value' => 2.0, 'options' => [] ],
                ]
            ];
    });
    
    $this->assertDatabaseHas('refract_cached_lens',
        [
            'id' => 1,
            'lens_type' => TotalFoodsLens::class,
            'filters_hash' => '0adbfaebef06813ff5873bf1d2911fe4ce4fc947dbdf36beb9b3c3ebd4d68df2',
            'filters' => json_encode([ 'max' => [ 'value' => 15, 'type' => 'float' ] ]),
            'state' => LensStates::READY->value,
            'meta_data' => json_encode([]),
            'last_requested_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);

    $this->assertDatabaseCount('refract_lens_paths', 2);
    $this->assertDatabaseHas('refract_lens_paths',
        [ 'cached_lens_id' => 1, 'path' => 'cheap', 'path_value' => json_encode([ 'value' => 1, 'options' => [] ]) ]);
    
    $this->assertDatabaseHas('refract_lens_paths',
        [ 'cached_lens_id' => 1, 'path' => 'expensive', 'path_value' => json_encode([ 'value' => 2, 'options' => [] ]) ]);

    
    $this->assertDatabaseCount('refract_lens_bands', 2);
    $this->assertDatabaseHas('refract_lens_bands',
        [ 'cached_lens_id' => 1, 'path' => 'cheap', 'splitter_id' => 1, 'band_index' => 1 ]);
    $this->assertDatabaseHas('refract_lens_bands',
        [ 'cached_lens_id' => 1, 'path' => 'expensive', 'splitter_id' => 1, 'band_index' => 2 ]);
});

test('update throws exception on incorrect id', function () {

    $service = new LensService();
    $service->update(12378);

})->throws(RefractException::class, "no cache entry found for id: 12378");

test('clear for given id', function () {
    $this->seed(LensSeeder::class);

    $cached = new CachedLens();
    $cached->lens_type = 'TestTypeLens';
    $cached->filters_hash = '';
    $cached->filters = [];
    $cached->state = LensStates::READY;
    $cached->last_requested_at = Carbon::now();
    $cached->save();

    $service = new LensService();
    $service->clear(cacheId: 1);

    $this->assertDatabaseCount(CachedLens::class, 1);
    $this->assertDatabaseHas(CachedLens::class, [ 'id' => 2 ]);

    $this->assertDatabaseCount('refract_lens_paths', 0);

    $this->assertDatabaseCount('refract_lens_bands', 0);

    
});

test('clear for given lens class', function () {
    $this->seed(LensSeeder::class);

    $cached = new CachedLens();
    $cached->lens_type = 'TestTypeLens';
    $cached->filters_hash = '';
    $cached->filters = [];
    $cached->state = LensStates::READY;
    $cached->last_requested_at = Carbon::now();
    $cached->save();

    $service = new LensService();
    $service->clear(lens: TotalFoodsLens::class);

    $this->assertDatabaseCount(CachedLens::class, 1);
    $this->assertDatabaseHas(CachedLens::class, [ 'id' => 2 ]);
    
    $this->assertDatabaseCount('refract_lens_paths', 0);

    $this->assertDatabaseCount('refract_lens_bands', 0);
});

test('clear truncates table', function () {
    $this->seed(LensSeeder::class);

    $cached = new CachedLens();
    $cached->lens_type = 'TestTypeLens';
    $cached->filters_hash = '';
    $cached->filters = [];
    $cached->state = LensStates::READY;
    $cached->last_requested_at = Carbon::now();
    $cached->save();

    $service = new LensService();
    $service->clear();

    $this->assertDatabaseCount(CachedLens::class, 0);
    
    $this->assertDatabaseCount('refract_lens_paths', 0);

    $this->assertDatabaseCount('refract_lens_bands', 0);

});

