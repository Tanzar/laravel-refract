<?php

namespace Tanzar\Refract\Support\Splitters;

use Illuminate\Support\Facades\DB;
use Tanzar\Refract\Config\RefractConfig;

final class Deltas
{
    /** @var array<int, float> */
    private array $values = [];

    private int $precision;

    public function __construct(private int $splitterId)
    {
        $this->precision = RefractConfig::precision();
    }

    public function add(int $band, float $change): void
    {
        $current = $this->values[$band] ?? 0;
        $this->values[$band] = round($current + $change, $this->precision);
    }

    /**
     * @return int[]
     */
    public function affectedBands(): array
    {
        return array_keys($this->values);
    }

    public function hasChanges(): bool
    {
        return $this->values !== [];
    }

    public function persist(): void
    {
        foreach ($this->values as $band => $value) {
            DB::table('refract_bands')
                ->where('splitter_id', $this->splitterId)
                ->where('band_index', $band)
                ->increment('current_value', $value);
        }
    }

    /**
     * @return array<int, float>
     */
    public function toArray(): array
    {
        return $this->values;
    }
}
