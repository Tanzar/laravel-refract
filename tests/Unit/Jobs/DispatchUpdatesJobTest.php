<?php

use Illuminate\Support\Facades\Queue;
use Tanzar\Refract\Jobs\DispatchUpdatesJob;
use Tanzar\Refract\Jobs\SplitterUpdateJob;
use Tanzar\Refract\Services\SplitterService;
use Workbench\App\Models\Food;

use function Orchestra\Testbench\workbench_path;

test('job dispatches update jobs', function () {
    config([
        'refract.discovery.namespace' => 'Workbench\\App\\',
        'refract.discovery.path' => workbench_path('app'), 
    ]);
    
    Queue::fake();

    $ids = [12, 15, 76, 33, 45];
    $service = new SplitterService();

    foreach($ids as $id) {
        $model = new Food();
        $model->id = $id;

        $service->addToUpdate($model);
    }

    $job = new DispatchUpdatesJob();

    $job->handle($service);

    Queue::assertPushed(SplitterUpdateJob::class, function (SplitterUpdateJob $job) {
        return $job->splitter === 'Workbench\App\Splitters\TotalFoodsSplitter' &&
            $job->modelIds === [12, 15, 76, 33, 45];
    });
});