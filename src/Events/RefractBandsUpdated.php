<?php

namespace Tanzar\Refract\Events;

final class RefractBandsUpdated
{
    /**
     * @param int $splitterId
     * @param array<int> $affectedBandIndices list of changed bands
     */
    public function __construct(
        public readonly int $splitterId,
        public readonly array $affectedBandIndices
    ) {}
}
