<?php

namespace Tanzar\Refract\Events;

class RefractBandsUpdated
{
    /**
     * @param int $splitterId
     * @param array<int> $affectedBandIndices list of changed bands
     * @param bool $isBatch was it updated in batch or singular
     */
    public function __construct(
        public readonly int $splitterId,
        public readonly array $affectedBandIndices,
        public readonly bool $isBatch = false
    ) {}
}
