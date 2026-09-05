<?php

namespace Tanzar\Refract\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Tanzar\Refract\Services\SplittersUpdateBuffer;
use Tanzar\Refract\Splitter\Splitter;

#[Tries(5)]
class DispatchBufferedUpdatesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        $this->onQueue(config('refract.splitters.queue', 'default'));
    }

    public function handle(SplittersUpdateBuffer $buffer): void
    {
        $config = $buffer->emptyBuffer();

        foreach ($config as $splitterClass => $modelIds) {
            /** @var class-string<Splitter> $splitterClass */
            SplitterUpdateJob::dispatch($splitterClass, $modelIds)
                ->afterCommit();
        }
    }
}
