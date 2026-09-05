<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tanzar\Refract\Jobs\DispatchBufferedUpdatesJob;
use Tanzar\Refract\Services\SplittersUpdateBuffer;
use Workbench\App\Models\Food;
use Workbench\App\Models\User;

use function Orchestra\Testbench\workbench_path;

beforeEach(function () {

    config([
        'refract.discovery.namespace' => 'Workbench\\App\\',
        'refract.discovery.path' => workbench_path('app'), 
    ]);
});

test('add method', function () {
    Queue::fake();

    $buffer = new SplittersUpdateBuffer();

    $buffer->add(new User());

    expect(Cache::has('splitters_update_buffer'))->toBeFalse();

    Queue::assertNothingPushed();

    $model = new Food();
    $model->name = 'name';
    $model->category = 'cat';
    $model->price = 10.0;
    $model->saveQuietly();

    $buffer->add($model);

    expect(Cache::has('splitters_update_buffer'))->toBeTrue();
    expect(Cache::get('splitters_update_buffer'))
        ->toBe([ 'Workbench\App\Splitters\TotalFoodsSplitter' => [ 1 ] ]);

    Queue::assertPushed(DispatchBufferedUpdatesJob::class);
});

test('empty buffer method', function () {
    Queue::fake();

    $buffer = new SplittersUpdateBuffer();
    
    $model = new Food();
    $model->name = 'name';
    $model->category = 'cat';
    $model->price = 10.0;
    $model->saveQuietly();

    $buffer->add($model);

    expect(Cache::has('splitters_update_buffer'))->toBeTrue();
    expect(Cache::get('splitters_update_buffer'))
        ->toBe([ 'Workbench\App\Splitters\TotalFoodsSplitter' => [ 1 ] ]);

    $array = $buffer->emptyBuffer();

    expect($array)
        ->toBe([ 'Workbench\App\Splitters\TotalFoodsSplitter' => [ 1 ] ]);


    expect(Cache::has('splitters_update_buffer'))->toBeFalse();
});