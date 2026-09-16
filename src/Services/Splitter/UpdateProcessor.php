<?php

namespace Tanzar\Refract\Services\Splitter;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Tanzar\Refract\Events\RefractBandsUpdated;
use Tanzar\Refract\Helpers\RefractHelper;
use Tanzar\Refract\Splitter\Splitter;

final class UpdateProcessor
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

        $structure = new BandStructureManager($this->splitter);
        $deltaCalculator = new DeltaCalculator($this->splitter->id(), $modelIds);

        foreach ($models as $model) {
            $params = $this->splitter->split($model);

            if ($params !== null) {
                $structure->analyze($params);

                $deltaCalculator->analyze($params);
            }
        }
        
        $existingHashesMap = $structure->verify();

        $deltaCalculator->calculate($existingHashesMap);

        $this->persist($deltaCalculator, $isBatch);
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

    private function persist(DeltaCalculator $calculator, bool $isBatch): void
    {
        if ($calculator->hasChanges()) {
            $calculator->getDeltas()->persist();
            $calculator->getPivots()
                ->persistUpdates()
                ->persistDeletes();

            event(new RefractBandsUpdated(
                $this->splitter->id(),
                $calculator->getDeltas()->affectedBands(),
                $isBatch
            ));
        }
    }

}
