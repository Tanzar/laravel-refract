<?php

namespace Tanzar\Refract\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Tanzar\Refract\Config\RefractConfig;
use Tanzar\Refract\Services\SplitterService;
use Tanzar\Refract\Splitter\Splitter;

#[Tries(5)]
final class DispatchUpdatesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        $this->onQueue(RefractConfig::splittersDefaultQueue());
    }

    public function handle(SplitterService $service): void
    {
        $config = $service->getBufferedUpdates();

        foreach ($config as $splitterClass => $modelIds) {
            /** @var class-string<Splitter> $splitterClass */
            SplitterUpdateJob::dispatch($splitterClass, $modelIds)
                ->afterCommit();
        }
    }
}
