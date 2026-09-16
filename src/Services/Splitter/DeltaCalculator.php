<?php

namespace Tanzar\Refract\Services\Splitter;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Tanzar\Refract\Helpers\RefractConfig;
use Tanzar\Refract\Splitter\SplitterParams;

final class DeltaCalculator
{
    private int $precision;
    
    /** @var Collection<int, SplitterParams> */
    private Collection $modelResults;

    /** @var Collection<int, object{band_index: int, current_value: float}> */
    private Collection $previous;
    
    private Deltas $deltas;
    private Pivots $pivots;

    /**
     * @param int $splitterId
     * @param mixed[] $requestedModelIds
     */
    public function __construct(int $splitterId, private array $requestedModelIds)
    {
        $this->precision = RefractConfig::precision();
        $this->modelResults = collect();
        $this->previous = collect();
        $this->deltas = new Deltas($splitterId);
        $this->pivots = new Pivots($splitterId);
        $this->previous = $this->loadPreviousStates($splitterId);
    }

    /**
     * @return Collection<int, object{band_index: int, current_value: float}>
     */
    private function loadPreviousStates(int $splitterId): Collection
    {
        /** @var Collection<int, object{band_index: int, current_value: float}> */
        return DB::table('refract_model_bands')
            ->where('splitter_id', $splitterId)
            ->whereIn('model_id', $this->requestedModelIds)
            ->get(['model_id', 'band_index', 'current_value'])
            ->keyBy('model_id');
    }

    public function analyze(SplitterParams $result): void
    {
        $modelId = $result->getModelId();
        $this->modelResults->put($modelId, $result);
    }

    public function getDeltas(): Deltas
    {
        return $this->deltas;
    }

    public function getPivots(): Pivots
    {
        return $this->pivots;
    }

    public function hasChanges(): bool
    {
        return $this->deltas->hasChanges() || $this->pivots->hasChanges();
    }

    /**
     * @param array<string, int> $hashToBandIndex
     * @return DeltaCalculator
     */
    public function calculate(
        array $hashToBandIndex
    ): self {

        /** @var SplitterParams $result */
        foreach ($this->modelResults as $result) {
            $this->processUpdatedModel($result, $hashToBandIndex);
        }

        $processedModelIds = $this->modelResults->keys()->all();
        $missingIds = array_diff($this->requestedModelIds, $processedModelIds);

        foreach ($missingIds as $missingId) {
            $this->processMissingModel($missingId);
        }

        return $this;
    }

    /**
     * @param SplitterParams $result
     * @param array<string, int> $hashToBandIndex
     * @return void
     */
    private function processUpdatedModel(
        SplitterParams $result,
        array $hashToBandIndex
    ): void {
        $newIndex = (int) $hashToBandIndex[$result->hash()];
        $value = $result->getModelValue();

        $prevState = $this->previous->get($result->getModelId());

        $oldIndex = $prevState?->band_index !== null ? (int) $prevState->band_index : null;
        $oldValue = (float) ($prevState->current_value ?? 0);

        if ($oldIndex === $newIndex && abs($oldValue - $value) < 0.00001) {
            return;
        }

        if ($oldIndex === $newIndex) {
            $diff = round($value - $oldValue, $this->precision);
            $this->deltas->add($newIndex, $diff);
        } else {
            if ($oldIndex !== null) {
                $this->deltas->add($oldIndex, -$oldValue);
            }
            $this->deltas->add($newIndex, $value);
        }

        $this->pivots->update($result, $newIndex, $value);
    }

    private function processMissingModel(int $missingId): void
    {
        $prevState = $this->previous->get($missingId);

        if ($prevState === null) {
            return;
        }

        $oldIndex = (int) $prevState->band_index;
        $oldValue = (float) $prevState->current_value;

        $this->deltas->add($oldIndex, -$oldValue);
        $this->pivots->delete($missingId);
    }
}
