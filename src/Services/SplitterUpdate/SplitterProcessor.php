<?php

namespace Tanzar\Refract\Services\SplitterUpdate;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Tanzar\Refract\Helpers\RefractHelper;
use Tanzar\Refract\Splitter\Splitter;

class SplitterProcessor
{
    private Splitter $splitter;

    /**
     * @param class-string<Splitter> $splitterClass
     * @param array<int> $modelIds
     */
    public function processChunk(string $splitterClass, array $modelIds, bool $isBatch = false): void
    {
        $this->splitter = RefractHelper::splitter($splitterClass);

        $models = $this->loadModels($modelIds);
        $previousStates = $this->loadPreviousStates($modelIds);

        $structure = new BandStructureManager($this->splitter);
        $deltaCalculator = new BandsDeltaCalculator();
        foreach ($models as $model) {
            $params = $this->splitter->split($model);

            if ($params !== null) {
                $structure->analyze($params);
                $deltaCalculator->analyze($params);
            }
        }
        
        $existingHashesMap = $structure->verify();

        $deltaCalculator->calculate($modelIds, $previousStates, $existingHashesMap);

        if ($deltaCalculator->hasChanges()) {
            (new BandsRepository($this->splitter))->persist($deltaCalculator, $isBatch);
        }
    }
    
    /**
     * @param int[] $modelIds
     * @return Collection<int, Model>
     */
    private function loadModels(array $modelIds): Collection
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = $this->splitter->modelClass();
        $key = (new $modelClass())->getKeyName();

        $query = $modelClass::query()->with($this->splitter->relations());

        if (in_array(SoftDeletes::class, class_uses_recursive($modelClass), true)) {
            /** @var mixed $queryWithSoftDeletes */
            $queryWithSoftDeletes = $query;
            $query = $queryWithSoftDeletes->withTrashed();
        }

        return $query->whereIn($key, $modelIds)->get();
    }

    /**
     * @param int[] $modelIds
     * @return Collection<int, object{band_index: int, current_value: float}>
     */
    private function loadPreviousStates(array $modelIds): Collection
    {
        /** @var Collection<int, object{band_index: int, current_value: float}> */
        return DB::table('refract_model_bands')
            ->where('splitter_id', $this->splitter->getDetails()->id)
            ->whereIn('model_id', $modelIds)
            ->get(['model_id', 'band_index', 'current_value'])
            ->keyBy('model_id');
    }

}
