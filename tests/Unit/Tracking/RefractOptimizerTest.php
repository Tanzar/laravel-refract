<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Tanzar\Refract\Services\RefractOptimizer;

use function Orchestra\Testbench\workbench_path;

beforeEach(function () {

    config([
        'refract.discovery.namespace' => 'Workbench\\App\\',
        'refract.discovery.path' => workbench_path('app'), 
    ]);
});

test('trackable map', function () {
    $optimizer = new RefractOptimizer();

    $map = $optimizer->getTrackableMap();

    expect($map)->toBe([
        'splitters' => [
            'Workbench\App\Models\Food' => [
                'Workbench\App\Splitters\TotalFoodsSplitter'
            ]
        ],
        'lens' => []
    ]);

});

test('trackable map, no classes', function () {
    config([
        'refract.discovery.namespace' => 'Workbench\\App\\',
        'refract.discovery.path' => workbench_path('app') . '/app/item.xlsx', 
    ]);


    $optimizer = new RefractOptimizer();

    $map = $optimizer->getTrackableMap();

    expect($map)->toBe([
        'splitters' => [],
        'lens' => []
    ]);

});


test('trackable map, not real path', function () {
    config([
        'refract.discovery.namespace' => 'Workbench\\App\\',
        'refract.discovery.path' => '/app/itemz', 
    ]);


    $optimizer = new RefractOptimizer();

    $map = $optimizer->getTrackableMap();

    expect($map)->toBe([
        'splitters' => [],
        'lens' => []
    ]);

});

test('oprimized file', function () {
    $path = base_path('bootstrap/cache/refract_track_map.php');
    $data = ['foo' => 'bar'];

    File::shouldReceive('exists')
        ->once()
        ->with($path)
        ->andReturn(true);

    File::shouldReceive('getRequire')
        ->once()
        ->with($path)
        ->andReturn($data);

    $optimizer = new RefractOptimizer();

    $map = $optimizer->getTrackableMap();

    expect($map)->toBe($data);
});

test('splitter in config, class not exist', function () {
    $path = base_path('bootstrap/cache/refract_track_map.php');
    $data = [
        'splitters' => [
            'Workbench\App\Models\Peon' => [
                'Workbench\App\Splitters\TotalFoodsSplitter'
            ],
            'Workbench\Database\Factories\UserFactory' => [
                'Workbench\App\Splitters\TotalFoodsSplitter'
            ]
        ]
    ];

    File::shouldReceive('exists')
        ->once()
        ->with($path)
        ->andReturn(true);

    File::shouldReceive('getRequire')
        ->once()
        ->with($path)
        ->andReturn($data);

    $optimizer = new RefractOptimizer();

    Log::shouldReceive('warning')
        ->once()
        ->with('RefractTracker: Model class Workbench\App\Models\Peon does not exist.');

    $optimizer->isTrackable('Workbench\App\Models\Peon');
});

test('splitter in config, classnot extending Model', function () {
    $path = base_path('bootstrap/cache/refract_track_map.php');
    $data = [
        'splitters' => [
            'Workbench\App\Models\Peon' => [
                'Workbench\App\Splitters\TotalFoodsSplitter'
            ],
            'Workbench\Database\Factories\UserFactory' => [
                'Workbench\App\Splitters\TotalFoodsSplitter'
            ]
        ]
    ];

    File::shouldReceive('exists')
        ->once()
        ->with($path)
        ->andReturn(true);

    File::shouldReceive('getRequire')
        ->once()
        ->with($path)
        ->andReturn($data);

    $optimizer = new RefractOptimizer();

    Log::shouldReceive('warning')
        ->once()
        ->with('RefractTracker: Class Workbench\Database\Factories\UserFactory is not an Eloquent model.');
    
    $optimizer->isTrackable('Workbench\Database\Factories\UserFactory');
});
