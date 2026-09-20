<?php

namespace Tanzar\Refract\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Tanzar\Refract\Observers\RefractModelObserver;
use Tanzar\Refract\Support\RefractFactory;

final class RefractTracker
{
    /** @var array<class-string<Model>, bool> $trackedModels */
    private array $trackedModels = [];

    public function initialize(): void
    {
        Event::listen('eloquent.booted: *', function (string $eventName) {

            /** @var class-string<Model> $model */
            $model = Str::of($eventName)->after('eloquent.booted: ')->toString();

            $map = RefractFactory::trackMap();

            if (!isset($this->trackedModels[$model]) && $map->isTrackable($model)) {
                $model::observe(RefractModelObserver::class);
                $this->trackedModels[$model] = true;
            }
        });
    }
}
