<?php

namespace Tanzar\Refract\Observers;

use Illuminate\Database\Eloquent\Model;
use Tanzar\Refract\Services\SplitterService;

final class RefractModelObserver
{
    public function __construct(private SplitterService $serivce)
    { }

    public function created(Model $model): void
    {
        $this->serivce->addToUpdate($model);
    }
 
    public function updated(Model $model): void
    {
        $this->serivce->addToUpdate($model);
    }
 
    public function deleted(Model $model): void
    {
        $this->serivce->addToUpdate($model);
    }
 
    public function restored(Model $model): void
    {
        $this->serivce->addToUpdate($model);
    }
 
    public function forceDeleted(Model $model): void
    {
        $this->serivce->addToUpdate($model);
    }
}
