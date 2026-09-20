<?php

namespace Tanzar\Refract\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Tanzar\Refract\Config\RefractConfig;
use Tanzar\Refract\Events\RefractBandsUpdated;
use Tanzar\Refract\Jobs\DispatchUpdatesJob;
use Tanzar\Refract\Splitter\Splitter;
use Tanzar\Refract\Support\BandStructureManager;
use Tanzar\Refract\Support\RefractFactory;
use Tanzar\Refract\Support\Splitters\DeltaCalculator;

class SplitterService
{
    public function addToUpdate(Model $model): void
    {
        $splitters = RefractFactory::trackMap()
            ->splitters()
            ->getForModel($model);

        if ($splitters === null) {
            return;
        }

        Cache::lock('splitters_update_buffer_lock', 10)->get(function () use ($model, $splitters) {
            $this->addToBuffer($model, $splitters);
        });
    }

    /**
     * @param Model $model
     * @param array<class-string> $splitters
     * @return void
     */
    private function addToBuffer(Model $model, array $splitters): void
    {
        /** @var array<class-string, array<int|string>> $buffer */
        $buffer = Cache::get('splitters_update_buffer', []);

        if ($buffer === []) {
            $this->dispatchUpdaterJob();
        }

        $modelKey = $model->getKey();

        foreach ($splitters as $splitterClass) {
            $buffer[$splitterClass][] = $modelKey;
        }
        
        Cache::put('splitters_update_buffer', $buffer, now()->addMinutes(5));
    }

    private function dispatchUpdaterJob(): void
    {
        dispatch(new DispatchUpdatesJob())
            ->delay(now()->addSeconds(RefractConfig::bufferDelay()))
            ->afterCommit();
    }

    /**
     * @return array<class-string, array<mixed>>
     */
    public function getBufferedUpdates(): array
    {
        /** @var array<class-string, array<class-string>> $buffered */
        $buffered = Cache::pull('splitters_update_buffer', []);
        return $buffered;
    }
    
    /**
     * @param class-string<Splitter> $splitter
     * @param mixed[] $models
     */
    public function update(string $splitter, array $models): void
    {
        $instance = RefractFactory::splitter($splitter);

        $loadedModels = $this->loadModels($instance, $models);

        $structure = new BandStructureManager($instance);
        $deltaCalculator = new DeltaCalculator($instance->id(), $models);

        foreach ($loadedModels as $model) {
            $params = $instance->split($model);

            if ($params !== null) {
                $structure->analyze($params);

                $deltaCalculator->analyze($params);
            }
        }
        
        $existingHashesMap = $structure->verify();

        $deltaCalculator->calculate($existingHashesMap);

        $this->persist($instance->id(), $deltaCalculator);
    }
    
    /**
     * @param int[] $modelIds
     * @return Collection<int, Model>
     */
    private function loadModels(Splitter $splitter, array $modelIds): Collection
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = $splitter->modelClass();
        $key = (new $modelClass())->getKeyName();

        $query = $modelClass::query()->with($splitter->relations());

        if (in_array(SoftDeletes::class, class_uses_recursive($modelClass), true)) {
            /** @var mixed $queryWithSoftDeletes */
            $queryWithSoftDeletes = $query;
            $query = $queryWithSoftDeletes->withTrashed();
        }

        return $query->whereIn($key, $modelIds)->get();
    }

    private function persist(int $splitterId, DeltaCalculator $calculator): void
    {
        if ($calculator->hasChanges()) {
            $calculator->getDeltas()->persist();
            $calculator->getPivots()
                ->persistUpdates()
                ->persistDeletes();

            event(new RefractBandsUpdated($splitterId, $calculator->getDeltas()->affectedBands()));
        }
    }


}
