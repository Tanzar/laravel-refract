<?php

use Mockery;
use Tanzar\Refract\Services\BandsDeltaCalculator;
use Tanzar\Refract\Splitter\SplitterParams;

beforeEach(function () {
    config()->set('refract.precision', 4);
});

test('returns empty state when no models are analyzed', function () {
    $calculator = new BandsDeltaCalculator();

    $calculator->calculate([], collect(), []);

    expect($calculator->hasChanges())->toBeFalse()
        ->and($calculator->getDeltas())->toBeEmpty()
        ->and($calculator->getPivotUpdates())->toBeEmpty()
        ->and($calculator->getPivotDeletes())->toBeEmpty()
        ->and($calculator->getAffectedBandIndices())->toBeEmpty();
});

test('adds new model to new band', function () {
    $calculator = new BandsDeltaCalculator();

    $param = Mockery::mock(SplitterParams::class);
    $param->shouldReceive('getModelId')->andReturn(10);
    $param->shouldReceive('hash')->andReturn('hash_a');
    $param->shouldReceive('getModelValue')->andReturn(5.0);

    $calculator->analyze($param);
    $calculator->calculate([10], collect(), ['hash_a' => 1]);

    expect($calculator->hasChanges())->toBeTrue()
        ->and($calculator->getDeltas())->toBe([1 => 5.0])
        ->and($calculator->getAffectedBandIndices())->toBe([1])
        ->and($calculator->getPivotUpdates())->toBe([
            ['model_id' => 10, 'band_index' => 1, 'value' => 5.0],
        ])
        ->and($calculator->getPivotDeletes())->toBeEmpty();
});

test('ignores model update when band index and value remain unchanged', function () {
    $calculator = new BandsDeltaCalculator();

    $param = Mockery::mock(SplitterParams::class);
    $param->shouldReceive('getModelId')->andReturn(10);
    $param->shouldReceive('hash')->andReturn('hash_a');
    $param->shouldReceive('getModelValue')->andReturn(5.0);

    $previousState = (object) ['band_index' => 1, 'current_value' => 5.0];
    $previousStates = collect([10 => $previousState]);

    $calculator->analyze($param);
    $calculator->calculate([10], $previousStates, ['hash_a' => 1]);

    expect($calculator->hasChanges())->toBeFalse()
        ->and($calculator->getDeltas())->toBeEmpty()
        ->and($calculator->getPivotUpdates())->toBeEmpty();
});

test('calculates value delta when model remains in the same band', function () {
    $calculator = new BandsDeltaCalculator();

    $param = Mockery::mock(SplitterParams::class);
    $param->shouldReceive('getModelId')->andReturn(10);
    $param->shouldReceive('hash')->andReturn('hash_a');
    $param->shouldReceive('getModelValue')->andReturn(12.5);

    $previousState = (object) ['band_index' => 1, 'current_value' => 5.0];
    $previousStates = collect([10 => $previousState]);

    $calculator->analyze($param);
    $calculator->calculate([10], $previousStates, ['hash_a' => 1]);

    expect($calculator->getDeltas())->toBe([1 => 7.5])
        ->and($calculator->getPivotUpdates())->toBe([
            ['model_id' => 10, 'band_index' => 1, 'value' => 12.5],
        ]);
});

test('shifts value between bands when model changes band', function () {
    $calculator = new BandsDeltaCalculator();

    $param = Mockery::mock(SplitterParams::class);
    $param->shouldReceive('getModelId')->andReturn(10);
    $param->shouldReceive('hash')->andReturn('hash_b');
    $param->shouldReceive('getModelValue')->andReturn(8.0);

    $previousState = (object) ['band_index' => 1, 'current_value' => 5.0];
    $previousStates = collect([10 => $previousState]);

    $calculator->analyze($param);
    $calculator->calculate([10], $previousStates, ['hash_b' => 2]);

    expect($calculator->getDeltas())->toBe([
        1 => -5.0,
        2 => 8.0,
    ])
    ->and($calculator->getAffectedBandIndices())->toBe([1, 2])
    ->and($calculator->getPivotUpdates())->toBe([
        ['model_id' => 10, 'band_index' => 2, 'value' => 8.0],
    ]);
});

test('handles missing model and marks it for pivot delete while subtracting delta', function () {
    $calculator = new BandsDeltaCalculator();

    $previousState = (object) ['band_index' => 3, 'current_value' => 15.0];
    $previousStates = collect([99 => $previousState]);

    $calculator->calculate([99, 100], $previousStates, []);

    expect($calculator->hasChanges())->toBeTrue()
        ->and($calculator->getDeltas())->toBe([3 => -15.0])
        ->and($calculator->getPivotDeletes())->toBe([99])
        ->and($calculator->getPivotUpdates())->toBeEmpty();
});

test('aggregates multiple model changes correctly across shared bands', function () {
    $calculator = new BandsDeltaCalculator();

    $m1 = Mockery::mock(SplitterParams::class);
    $m1->shouldReceive('getModelId')->andReturn(1);
    $m1->shouldReceive('hash')->andReturn('hash_a');
    $m1->shouldReceive('getModelValue')->andReturn(15.0);

    $m2 = Mockery::mock(SplitterParams::class);
    $m2->shouldReceive('getModelId')->andReturn(2);
    $m2->shouldReceive('hash')->andReturn('hash_b');
    $m2->shouldReceive('getModelValue')->andReturn(20.0);

    $previousStates = collect([
        1 => (object) ['band_index' => 1, 'current_value' => 10.0],
        2 => (object) ['band_index' => 1, 'current_value' => 5.0],
    ]);

    $calculator->analyze($m1);
    $calculator->analyze($m2);

    $calculator->calculate([1, 2], $previousStates, [
        'hash_a' => 1,
        'hash_b' => 2,
    ]);

    expect($calculator->getDeltas())->toBe([
        1 => 0.0,
        2 => 20.0,
    ])
    ->and($calculator->getPivotUpdates())->toHaveCount(2)
    ->and($calculator->getPivotDeletes())->toBeEmpty();
});