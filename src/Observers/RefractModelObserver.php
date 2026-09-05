<?php

namespace Tanzar\Refract\Observers;

use Illuminate\Database\Eloquent\Model;
use Tanzar\Refract\Services\SplittersUpdateBuffer;

class RefractModelObserver
{
    public function __construct(private SplittersUpdateBuffer $buffer)
    { }

    public function created(Model $model): void
    {
        $this->buffer->add($model);
    }
 
    public function updated(Model $model): void
    {
        $this->buffer->add($model);
    }
 
    public function deleted(Model $model): void
    {
        $this->buffer->add($model);
    }
 
    public function restored(Model $model): void
    {
        $this->buffer->add($model);
    }
 
    public function forceDeleted(Model $model): void
    {
        $this->buffer->add($model);
    }


}
