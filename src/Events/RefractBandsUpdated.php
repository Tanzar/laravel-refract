<?php

namespace Tanzar\Refract\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class RefractBandsUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    /**
     * @param int $splitterId
     * @param array<int> $affectedBandIndices list of changed bands
     */
    public function __construct(
        public readonly int $splitterId,
        public readonly array $affectedBandIndices
    ) {}
}
