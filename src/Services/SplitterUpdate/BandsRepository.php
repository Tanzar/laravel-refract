<?php

namespace Tanzar\Refract\Services\SplitterUpdate;

use Illuminate\Support\Facades\DB;
use Tanzar\Refract\Events\RefractBandsUpdated;
use Tanzar\Refract\Splitter\Splitter;

class BandsRepository
{
    public function __construct(private Splitter $splitter) {}

    public function persist(BandsDeltaCalculator $calculator, bool $isBatch = false): void
    {
        DB::transaction(function () use ($calculator): void {
            $this->applyDeltas($calculator);
            $this->syncPivotUpdates($calculator);
            $this->syncPivotDeletes($calculator);
        });

        event(new RefractBandsUpdated(
            $this->splitter->getDetails()->id,
            $calculator->getAffectedBandIndices(),
            $isBatch
        ));
    }

    private function applyDeltas(BandsDeltaCalculator $calculator): void
    {
        $deltas = $calculator->getDeltas();
        if ($deltas === []) {
            return;
        }

        foreach ($deltas as $bandIndex => $deltaValue) {
            DB::table('refract_bands')
                ->where('splitter_id', $this->splitter->getDetails()->id)
                ->where('band_index', $bandIndex)
                ->increment('current_value', $deltaValue);
        }
    }

    private function syncPivotUpdates(BandsDeltaCalculator $calculator): void
    {
        $pivotUpdates = $calculator->getPivotUpdates();
        if ($pivotUpdates === []) {
            return;
        }

        $records = collect($pivotUpdates)
            ->map(fn (array $update) => [
                'splitter_id' => $this->splitter->getDetails()->id,
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
    }

    private function syncPivotDeletes(BandsDeltaCalculator $calculator): void
    {
        $pivotDeletes = $calculator->getPivotDeletes();
        if ($pivotDeletes === []) {
            return;
        }

        DB::table('refract_model_bands')
            ->where('splitter_id', $this->splitter->getDetails()->id)
            ->whereIn('model_id', $pivotDeletes)
            ->delete();
    }
}
