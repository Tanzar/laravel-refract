<?php

namespace Tanzar\Refract\Services;

use Illuminate\Support\Collection;
use Tanzar\Refract\Splitter\SplitterParams;

class BandsDeltaCalculator
{
private int $precision;
    
    /** @var Collection<int, SplitterParams> */
    private Collection $modelResults;
    
    /** @var array<int, float> $deltas */
    private array $deltas = [];

    /** @var array<int, array{model_id: int, band_index: int, value: float}> */
    private array $pivotUpdates = [];

    /** @var array<int, int> */
    private array $pivotDeletes = [];

    public function __construct()
    {
        $this->precision = (int) config('refract.precision', 10);
        $this->modelResults = new Collection();
    }

    public function analyze(SplitterParams $result): void
    {
        $this->modelResults->put($result->getModelId(), $result);
    }

    /**
     * @return array<int, float>
     */
    public function getDeltas(): array
    {
        return $this->deltas;
    }

    /**
     * @return array<int, array{model_id: int, band_index: int, value: float}>
     */
    public function getPivotUpdates(): array
    {
        return $this->pivotUpdates;
    }

    /**
     * @return array<int, int>
     */
    public function getPivotDeletes(): array
    {
        return $this->pivotDeletes;
    }

    public function hasChanges(): bool
    {
        return $this->deltas !== [] || $this->pivotUpdates !== [] || $this->pivotDeletes !== [];
    }

    /**
     * @return int[]
     */
    public function getAffectedBandIndices(): array
    {
        return array_keys($this->deltas);
    }

    /**
     * @param int[] $requestedModelIds
     * @param Collection<int, object{band_index: int, current_value: float}> $previousStates
     * @param array<string, int> $hashToBandIndex
     * @return BandsDeltaCalculator
     */
    public function calculate(
        array $requestedModelIds,
        Collection $previousStates,
        array $hashToBandIndex
    ): self {
        $this->deltas = [];
        $this->pivotUpdates = [];
        $this->pivotDeletes = [];

        /** @var SplitterParams $result */
        foreach ($this->modelResults as $result) {
            $this->processUpdatedModel($result, $previousStates, $hashToBandIndex);
        }

        $processedModelIds = $this->modelResults->keys()->all();
        $missingIds = array_diff($requestedModelIds, $processedModelIds);

        foreach ($missingIds as $missingId) {
            $prevState = $previousStates->get($missingId);
            $this->processMissingModel($missingId, $prevState);
        }

        return $this;
    }

    /**
     * @param SplitterParams $result
     * @param Collection<int, object{band_index: int, current_value: float}> $previousStates
     * @param array<string, int> $hashToBandIndex
     * @return void
     */
    private function processUpdatedModel(
        SplitterParams $result,
        Collection $previousStates,
        array $hashToBandIndex
    ): void {
        $newIndex = (int) $hashToBandIndex[$result->hash()];
        $value = $result->getModelValue();

        $prevState = $previousStates->get($result->getModelId());

        $oldIndex = $prevState?->band_index !== null ? (int) $prevState->band_index : null;
        $oldValue = (float) ($prevState->current_value ?? 0);

        if ($oldIndex === $newIndex && abs($oldValue - $value) < 0.00001) {
            return;
        }

        if ($oldIndex === $newIndex) {
            $diff = round($value - $oldValue, $this->precision);
            $this->applyDelta($newIndex, $diff);
        } else {
            if ($oldIndex !== null) {
                $this->applyDelta($oldIndex, -$oldValue);
            }
            $this->applyDelta($newIndex, $value);
        }

        $this->pivotUpdates[] = [
            'model_id' => $result->getModelId(),
            'band_index' => $newIndex,
            'value' => $value,
        ];
    }

    /**
     * @param object{band_index: int, current_value: float}|null $prevState
     */
    private function processMissingModel(int $missingId, ?object $prevState): void
    {
        if ($prevState === null) {
            return;
        }

        $oldIndex = (int) $prevState->band_index;
        $oldValue = (float) $prevState->current_value;

        $this->applyDelta($oldIndex, -$oldValue);
        $this->pivotDeletes[] = $missingId;
    }

    private function applyDelta(int $bandIndex, float $amount): void
    {
        $current = $this->deltas[$bandIndex] ?? 0;
        $this->deltas[$bandIndex] = round($current + $amount, $this->precision);
    }
}
