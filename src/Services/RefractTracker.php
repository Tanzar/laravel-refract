<?php

namespace Tanzar\Refract\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Tanzar\Refract\Observers\RefractModelObserver;

class RefractTracker
{
    /** @var array<class-string<Model>, bool> $trackedModels */
    private array $trackedModels = [];

    public function __construct(private RefractOptimizer $optimizer) { }


    public function initialize(): void
    {
        Event::listen('eloquent.booted: *', function (string $eventName) {

            /** @var class-string<Model> $model */
            $model = Str::of($eventName)->after('eloquent.booted: ')->toString();

            if (
                isset($this->trackedModels[$model]) ||
                !$this->optimizer->isTrackable($model)
            ) {
                return;
            }

            $model::observe(RefractModelObserver::class);
            $this->trackedModels[$model] = true;
        });
    }

}
