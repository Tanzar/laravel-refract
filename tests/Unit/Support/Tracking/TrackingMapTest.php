<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use \Mockery;
use Tanzar\Refract\Support\Tracking\TrackingMap;

use function Orchestra\Testbench\workbench_path;

beforeEach(function () {
    config([
        'refract.discovery.namespace' => 'Workbench\\App\\',
        'refract.discovery.path' => workbench_path('app'), 
    ]);
});

test('splitter in config, class not exist', function () {
    $path = app()->bootstrapPath('cache/refract_track_map.php');
    
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

    Log::shouldReceive('warning')
        ->once()
        ->with('RefractTracker: Model class Workbench\App\Models\Peon does not exist.');

    new TrackingMap()->isTrackable('Workbench\App\Models\Peon');
});

test('splitter in config, classnot extending Model', function () {
    $path = app()->bootstrapPath('cache/refract_track_map.php');
    
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

    Log::shouldReceive('warning')
        ->once()
        ->with('RefractTracker: Class Workbench\Database\Factories\UserFactory is not an Eloquent model.');
    
    new TrackingMap()->isTrackable('Workbench\Database\Factories\UserFactory');
});

test('creates cache file', function () {
    File::spy();

    new TrackingMap()->saveCache();

    File::shouldHaveReceived('put')
        ->once()
        ->with(
            Mockery::on(fn($path) => str_contains($path, 'refract_track_map.php')),
            Mockery::type('string')
        );
});