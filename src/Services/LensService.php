<?php

namespace Tanzar\Refract\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Sleep;
use Tanzar\Refract\Config\RefractConfig;
use Tanzar\Refract\Enums\LensStates;
use Tanzar\Refract\Events\RefractLensUpdated;
use Tanzar\Refract\Exceptions\RefractException;
use Tanzar\Refract\Models\CachedLens;
use Tanzar\Refract\Support\Filters\Filters;
use Tanzar\Refract\Support\Lens\Lens;
use Tanzar\Refract\Support\Lens\LensSynchronizer;
use Tanzar\Refract\Support\Lens\Parser\CachedLensParser;
use Tanzar\Refract\Support\Lens\Parser\LensRootParser;
use Tanzar\Refract\Support\RefractFactory;
use Throwable;

class LensService
{

    /**
     * @param string $lens
     * @param Filters $filters
     * @return array{lens: array{cacheId: int}, meta: array<string, mixed>, nodes: array<string, mixed>}
     */
    public function get(string $lens, Filters $filters): array
    {
        $lensInstance = RefractFactory::lens($lens);
        $cached = $this->getCached($lensInstance::class, $filters);

        if ($cached) {
            return $this->getExisting($cached, $lensInstance, $filters);
        }
        return $this->generateNew($lensInstance, $filters);
    }

    private function getCached(string $lensType, Filters $filters): ?CachedLens
    {
        return CachedLens::query()
            ->where('lens_type', $lensType)
            ->where('filters_hash', $filters->hash())
            ->first();
    }

    /**

     * @param CachedLens $cached
     * @param Lens $lens
     * @param Filters $filters
     * @return array{lens: array{cacheId: int}, meta: array<string, mixed>, nodes: array<string, mixed>}
     */
    private function getExisting(CachedLens $cached, Lens $lens, Filters $filters): array
    {
        if ($cached->state !== LensStates::READY) {
            $this->waitForStatus($cached);
        }
        
        if ($cached->state === LensStates::READY) {
            return $this->loadCached($cached);
        }
        
        $root = $lens->generate($filters);
        return (new LensRootParser($cached, $root))->parse();
    }

    /**
     * @param CachedLens $cached
     * @return array{lens: array{cacheId: int}, meta: array<string, mixed>, nodes: array<string, mixed>}
     */
    private function loadCached(CachedLens $cached): array
    {
        $cached->last_requested_at = Carbon::now();
        $cached->saveQuietly();
        
        return (new CachedLensParser($cached))
            ->parse();
    }

    private function waitForStatus(CachedLens $cached): void
    {
        $attempts = RefractConfig::lensGetAttempts();
        $delay = RefractConfig::lensGetRetryDelay();
        $attempt = 1;
        while ($attempt <= $attempts && $cached->state !== LensStates::READY) {
            Sleep::for($delay)->seconds();
            $cached->refresh();
            $attempt++;
        }
    }

    /**
     * @param Lens $lens
     * @param Filters $filters
     * @return array{lens: array{cacheId: int}, meta: array<string, mixed>, nodes: array<string, mixed>}
     */
    private function generateNew(Lens $lens, Filters $filters): array
    {
        $cached = $this->generateNewCacheEntry($lens, $filters);
        $root = $lens->generate($filters);

        try {
            RefractFactory::synchronizer($cached)->sync($root);
        }
        catch (Throwable $e) {
            $cached->delete();
            throw $e;
        }
        $cached->meta_data = $root->meta()->toArray();
        $cached->state = LensStates::READY;
        $cached->save();

        return (new LensRootParser($cached, $root))->parse();
    }

    private function generateNewCacheEntry(Lens $lens, Filters $filters): CachedLens
    {
        $cached = new CachedLens();
        $cached->lens_type = $lens::class;
        $cached->filters_hash = $filters->hash();
        $cached->filters = $filters->toArray();
        $cached->state = LensStates::GENERATING;
        $cached->last_requested_at = Carbon::now();
        $cached->save();
        return $cached;
    }

    public function update(int $cachedId): void
    {
        $cached = $this->getCachedById($cachedId);

        $filters = Filters::fromCached($cached);
        $lens = RefractFactory::lens($cached->lens_type);
        $root = $lens->generate($filters);
        new LensSynchronizer($cached->id)->sync($root);

        RefractLensUpdated::dispatch(
            $cached->id,
            new LensRootParser($cached, $root)->parse()
        );
    }

    private function getCachedById(int $id): CachedLens
    {
        $cached = CachedLens::find($id);
        if ($cached === null) {
            throw new RefractException("no cache entry found for id: $id");
        }
        return $cached;
    }

    public function clear(?int $cacheId = null, ?string $lens = null): void
    {
        if ($cacheId) {
            $this->deleteByID($cacheId);
        } else if ($lens) {
            $this->deleteByName($lens);
        } else {
            DB::table('refract_lens_paths')->truncate();
            DB::table('refract_lens_bands')->truncate();
            CachedLens::truncate();
        }
    }

    private function deleteByID(int $id): void
    {
        DB::transaction(function () use ($id) {
            DB::table('refract_lens_paths')
                ->whereIn('cached_lens_id', function (Builder $query) use ($id) {
                    $query->select('id')
                        ->from('refract_cached_lens')
                        ->where('id', $id);
                })->delete();

            DB::table('refract_lens_bands')
                ->whereIn('cached_lens_id', function (Builder $query) use ($id) {
                    $query->select('id')
                        ->from('refract_cached_lens')
                        ->where('id', $id);
                })->delete();

            DB::table('refract_cached_lens')->where('id', $id)->delete();
        });
    }

    private function deleteByName(string $name): void
    {
        $lensType = RefractFactory::lens($name)::class;

        DB::transaction(function () use ($lensType) {
            DB::table('refract_lens_paths')
                ->whereIn('cached_lens_id', function (Builder $query) use ($lensType) {
                    $query->select('id')
                        ->from('refract_cached_lens')
                        ->where('lens_type', $lensType);
                })->delete();

            DB::table('refract_lens_bands')
                ->whereIn('cached_lens_id', function (Builder $query) use ($lensType) {
                    $query->select('id')
                        ->from('refract_cached_lens')
                        ->where('lens_type', $lensType);
                })->delete();

            DB::table('refract_cached_lens')->where('lens_type', $lensType)->delete();
        });
    }
}
