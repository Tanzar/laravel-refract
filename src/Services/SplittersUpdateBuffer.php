<?php

namespace Tanzar\Refract\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Tanzar\Refract\Helpers\RefractHelper;
use Tanzar\Refract\Jobs\DispatchBufferedUpdatesJob;

class SplittersUpdateBuffer
{
    
    public function add(Model $model): void
    {
        $splitters = RefractHelper::optimizer()->getSplitters($model);
        if ($splitters === null) {
            return;
        }

        Cache::lock('splitters_update_buffer_lock', 10)->get(function () use ($model, $splitters) {
            $this->addToBuffer($model, $splitters);
        });
    }

    /**
     * @param Model $model
     * @param array<class-string> $splitters
     * @return void
     */
    private function addToBuffer(Model $model, array $splitters): void
    {
        /** @var array<class-string, array<int|string>> $buffer */
        $buffer = Cache::get('splitters_update_buffer', []);

        if ($buffer === []) {
            $this->dispatchUpdaterJob();
        }

        $modelKey = $model->getKey();

        foreach ($splitters as $splitterClass) {
            $buffer[$splitterClass][] = $modelKey;
        }
        
        Cache::put('splitters_update_buffer', $buffer, now()->addMinutes(5));
    }

    private function dispatchUpdaterJob(): void
    {
        dispatch(new DispatchBufferedUpdatesJob())
            ->delay(now()->addSeconds(config('refract.buffer_delay', 10)))
            ->afterCommit();
    }

    /**
     * @return array<class-string, array<mixed>>
     */
    public function emptyBuffer(): array
    {
        /** @var array<class-string, array<class-string>> $buffered */
        $buffered = Cache::pull('splitters_update_buffer', []);
        return $buffered;
    }
}
