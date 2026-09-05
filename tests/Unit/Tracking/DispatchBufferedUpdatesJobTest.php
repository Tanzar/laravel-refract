<?php

use Illuminate\Support\Facades\Queue;
use Mockery;
use Tanzar\Refract\Jobs\DispatchBufferedUpdatesJob;
use Tanzar\Refract\Jobs\SplitterUpdateJob;
use Tanzar\Refract\Services\SplittersUpdateBuffer;

test('job dispatches update jobs', function () {
    Queue::fake();

    $mock = Mockery::mock(SplittersUpdateBuffer::class);
    $mock->shouldReceive('emptyBuffer')->andReturn([
        'Workbench\App\Splitters\TotalFoodsSplitter' => [12, 15, 76, 33, 45]
    ]);

    $job = new DispatchBufferedUpdatesJob();

    $job->handle($mock);

    Queue::assertPushed(SplitterUpdateJob::class, function (SplitterUpdateJob $job) {
        return $job->splitter === 'Workbench\App\Splitters\TotalFoodsSplitter' &&
            $job->modelIds === [12, 15, 76, 33, 45];
    });
});