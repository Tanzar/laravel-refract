<?php

namespace Tanzar\Refract\Support\Splitters;

use Illuminate\Support\Facades\DB;
use Tanzar\Refract\Splitter\SplitterParams;

final class Pivots
{
    /** @var array<int, array{model_id: int, band_index: int, value: float}> */
    private array $updates = [];

    /** @var array<int, int> */
    private array $deletes = [];

    public function __construct(private int $splitterId) {}

    public function update(SplitterParams $result, int $newIndex, float $value): void
    {
        $this->updates[] = [
            'model_id' => $result->getModelId(),
            'band_index' => $newIndex,
            'value' => $value,
        ];
    }

    public function delete(mixed $modelId): void
    {
        $this->deletes[] = $modelId;
    }

    public function hasChanges(): bool
    {
        return $this->updates !== [] || $this->deletes !== [];
    }

    public function persistUpdates(): self
    {
        $records = collect($this->updates)
            ->map(fn (array $update) => [
                'splitter_id' => $this->splitterId,
                'model_id' => $update['model_id'],
                'band_index' => $update['band_index'],
                'current_value' => $update['value'],
            ])
            ->all();

        DB::table('refract_model_bands')->upsert(
            $records,
            ['splitter_id', 'model_id'],
            ['band_index', 'current_value']
        );

        return $this;
    }

    public function persistDeletes(): self
    {
        DB::table('refract_model_bands')
            ->where('splitter_id', $this->splitterId)
            ->whereIn('model_id', $this->deletes)
            ->delete();

        return $this;
    }

    /**
     * @return array{deletes: int[], updates: array<int, array{model_id: int, band_index: int, value: float}>}
     */
    public function toArray(): array
    {
        return [
            'updates' => $this->updates,
            'deletes' => $this->deletes
        ];
    }
}
