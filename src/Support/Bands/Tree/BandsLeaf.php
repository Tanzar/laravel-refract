<?php

namespace Tanzar\Refract\Support\Bands\Tree;

final class BandsLeaf
{
    private float $value = 0.0;
    /** @var int[] */
    private array $bands = [];

    public function add(int $band, float $value): void
    {
        if (!in_array($band, $this->bands)) {
            $this->bands[] = $band;
            $this->value += $value;
        }
    }

    public function getValue(): float
    {
        return $this->value;
    }

    /**
     * @return int[]
     */
    public function getBands(): array
    {
        return $this->bands;
    }
}
