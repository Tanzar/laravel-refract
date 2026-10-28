<?php

namespace Tanzar\Refract\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RefractLensUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param int $cachedLensId
     * @param array<string, mixed> $state
     */
    public function __construct(
        public int $cachedLensId,
        public array $state
    ) { }
}
