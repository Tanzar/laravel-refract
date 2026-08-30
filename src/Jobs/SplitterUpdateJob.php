<?php

namespace Tanzar\Refract\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Tanzar\Refract\Services\SplitterUpdate\SplitterProcessor;
use Tanzar\Refract\Splitter\Splitter;

#[Tries(5)]
#[Backoff([ 5, 10, 30 ])]
class SplitterUpdateJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 
     * @param class-string<Splitter> $splitter
     * @param int[] $modelIds
     */
    public function __construct(
        public string $splitter,
        public array $modelIds = []
    ) { }

    public function handle(SplitterProcessor $processor): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $processor->processChunk(
            $this->splitter,
            $this->modelIds,
            $this->batchId !== null
        );
    }
}
