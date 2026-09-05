<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tanzar\Refract\Jobs\DispatchBufferedUpdatesJob;
use Workbench\App\Models\Food;

use function Orchestra\Testbench\workbench_path;

beforeEach(function () {

    config([
        'refract.discovery.namespace' => 'Workbench\\App\\',
        'refract.discovery.path' => workbench_path('app'), 
    ]);
});

test('model creation', function() {
    Queue::fake();

    $food = new Food();
    $food->name = 'Test Food';
    $food->category = 'general';
    $food->price = 10.0;
    $food->save();

    Queue::assertPushed(DispatchBufferedUpdatesJob::class);
});

test('model quiet selection', function() {
    Queue::fake();

    $food = new Food();
    $food->name = 'Test Food';
    $food->category = 'general';
    $food->price = 10.0;
    $food->saveQuietly();

    Queue::assertNotPushed(DispatchBufferedUpdatesJob::class);
});



test('model update', function() {
    Queue::fake();

    DB::table('food')->insert([
        'name' => 'Test Food',
        'category' => 'general',
        'price' => 10.0,
        'created_at' => now(),
        'updated_at' => now()
    ]);

    $food = Food::first();
    $food->price = 15.0;
    $food->save();

    Queue::assertPushed(DispatchBufferedUpdatesJob::class);
});

test('model soft delete', function() {
    Queue::fake();

    DB::table('food')->insert([
        'name' => 'Test Food',
        'category' => 'general',
        'price' => 10.0,
        'created_at' => now(),
        'updated_at' => now()
    ]);

    Food::first()->delete();

    Queue::assertPushed(DispatchBufferedUpdatesJob::class);
});


test('model restore', function() {
    Queue::fake();

    DB::table('food')->insert([
        'name' => 'Test Food',
        'category' => 'general',
        'price' => 10.0,
        'created_at' => now(),
        'updated_at' => now(),
        'deleted_at' => now()
    ]);

    Food::withTrashed()->first()->restore();

    Queue::assertPushed(DispatchBufferedUpdatesJob::class);
});


test('model force delete', function() {
    Queue::fake();

    DB::table('food')->insert([
        'name' => 'Test Food',
        'category' => 'general',
        'price' => 10.0,
        'created_at' => now(),
        'updated_at' => now()
    ]);

    Food::first()->forceDelete();

    Queue::assertPushed(DispatchBufferedUpdatesJob::class);
});

