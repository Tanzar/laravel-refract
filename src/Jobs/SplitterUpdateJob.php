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
use Tanzar\Refract\Services\SplitterService;
use Tanzar\Refract\Splitter\Splitter;
use Tanzar\Refract\Support\RefractFactory;

#[Tries(5)]
#[Backoff([ 5, 10, 30 ])]
final class SplitterUpdateJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 
     * @param class-string<Splitter> $splitter
     * @param int[] $modelIds
     */
    public function __construct(public string $splitter, public array $modelIds = [])
    {
        $queue = RefractFactory::splitter($this->splitter)->queue();

        $this->onQueue($queue);
    }

    public function handle(SplitterService $service): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $service->update($this->splitter, $this->modelIds);
    }
}
