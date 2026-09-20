<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tanzar\Refract\Jobs\DispatchUpdatesJob;
use Tanzar\Refract\Services\SplitterService;
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

    $service = new SplitterService();

    $service->addToUpdate(new User());

    expect(Cache::has('splitters_update_buffer'))->toBeFalse();

    Queue::assertNothingPushed();

    $model = new Food();
    $model->name = 'name';
    $model->category = 'cat';
    $model->price = 10.0;
    $model->saveQuietly();

    $service->addToUpdate($model);

    expect(Cache::has('splitters_update_buffer'))->toBeTrue();
    expect(Cache::get('splitters_update_buffer'))
        ->toBe([ 'Workbench\App\Splitters\TotalFoodsSplitter' => [ 1 ] ]);

    Queue::assertPushed(DispatchUpdatesJob::class);
});

test('empty buffer method', function () {
    Queue::fake();

    $service = new SplitterService();

    $model = new Food();
    $model->name = 'name';
    $model->category = 'cat';
    $model->price = 10.0;
    $model->saveQuietly();

    $service->addToUpdate($model);

    expect(Cache::has('splitters_update_buffer'))->toBeTrue();
    expect(Cache::get('splitters_update_buffer'))
        ->toBe([ 'Workbench\App\Splitters\TotalFoodsSplitter' => [ 1 ] ]);

    $array = $service->getBufferedUpdates();

    expect($array)
        ->toBe([ 'Workbench\App\Splitters\TotalFoodsSplitter' => [ 1 ] ]);


    expect(Cache::has('splitters_update_buffer'))->toBeFalse();
});