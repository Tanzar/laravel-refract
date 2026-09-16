<?php

use Illuminate\Support\Facades\Queue;
use Mockery;
use Tanzar\Refract\Jobs\DispatchBufferedUpdatesJob;
use Tanzar\Refract\Jobs\SplitterUpdateJob;
use Tanzar\Refract\Services\UpdateBuffer;
use Workbench\App\Models\Food;

use function Orchestra\Testbench\workbench_path;

test('job dispatches update jobs', function () {
    config([
        'refract.discovery.namespace' => 'Workbench\\App\\',
        'refract.discovery.path' => workbench_path('app'), 
    ]);
    
    Queue::fake();

    $ids = [12, 15, 76, 33, 45];
    $buffer = new UpdateBuffer();

    foreach($ids as $id) {
        $model = new Food();
        $model->id = $id;

        $buffer->add($model);
    }

    $job = new DispatchBufferedUpdatesJob();

    $job->handle($buffer);

    Queue::assertPushed(SplitterUpdateJob::class, function (SplitterUpdateJob $job) {
        return $job->splitter === 'Workbench\App\Splitters\TotalFoodsSplitter' &&
            $job->modelIds === [12, 15, 76, 33, 45];
    });
});