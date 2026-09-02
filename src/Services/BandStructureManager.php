<?php

namespace Tanzar\Refract\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Tanzar\Refract\Splitter\Splitter;
use Tanzar\Refract\Splitter\SplitterParams;

class BandStructureManager
{
    /** @var Collection<string, mixed[]> $paramsToInsert */
    private Collection $paramsToInsert;

    /** @var Collection<string, string[]> $bandParamsMap */
    private Collection $bandParamsMap;

    public function __construct(private Splitter $splitter)
    {
        $this->paramsToInsert = collect();
        $this->bandParamsMap = collect();
    }


    public function analyze(SplitterParams $params): void
    {
        $bandHash = $params->hash();

        if ($this->bandParamsMap->has($bandHash)) {
            return;
        }

        $bandParams = [];
        foreach ($params->getKeys() as $keyName) {
            $paramTempKey = $params->getType($keyName)->value . ':' . $params->getValue($keyName);
            $this->paramsToInsert->put($paramTempKey, $params->toInsert($keyName));
            $bandParams[$keyName] = $paramTempKey;
        }

        $this->bandParamsMap->put($bandHash, $bandParams);
    }
    
    /**
     * @return array<string, int>
     */
    public function verify(): array
    {
        $this->persistParameters();

        return $this->resolveBands();
    }

    private function persistParameters(): void
    {
        if ($this->paramsToInsert->isEmpty()) {
            return;
        }

        $requestedKeys = $this->paramsToInsert->keys();

        $existingKeys = DB::table('refract_params')
            ->whereIn(DB::raw("CONCAT(`type`, ':', `raw_value`)"), $requestedKeys)
            ->pluck(DB::raw("CONCAT(`type`, ':', `raw_value`)"))
            ->all();

        $existingKeysMap = array_flip($existingKeys);

        $missingParameters = [];
        foreach ($this->paramsToInsert as $uniqueKey => $insert) {
            if (!isset($existingKeysMap[$uniqueKey])) {
                $missingParameters[] = $insert;
            }
        }

        if ($missingParameters !== []) {
            DB::table('refract_params')->insertOrIgnore($missingParameters);
        }
    }

    /**
     * @return array<string, int>
     */
    private function resolveBands(): array
    {
        $existingBands = $this->getExistingBandsMap();

        $missingHashes = $this->bandParamsMap->keys()->diff($existingBands->keys())->values();

        if ($missingHashes->isNotEmpty()) {
            return $this->updateBandsStructure($missingHashes, $existingBands);
        }
        return $existingBands->toArray();
    }

    /**
     * @return Collection<string, int>
     */
    private function getExistingBandsMap(): Collection
    {
        return DB::table('refract_bands')
            ->where('splitter_id', $this->splitter->getDetails()->id)
            ->whereIn('signature_hash', $this->bandParamsMap->keys())
            ->pluck('band_index', 'signature_hash');
    }

    /**
     * @param Collection<int, string> $missingHashes
     * @param Collection<string, int> $existingHashes
     * @return array<string, int>
     */
    private function updateBandsStructure(Collection $missingHashes, Collection $existingHashes): array
    {
        $reservedIndexes = $this->reserveIndexes($missingHashes->count());

        $paramMap = DB::table('refract_params')
            ->select('id', DB::raw("CONCAT(`type`, ':', `raw_value`) as key"))
            ->get()
            ->keyBy('key')
            ->map(fn (object $object) => $object->id);

        $bandsToInsert = [];
        $pivotToInsert = [];

        foreach ($missingHashes as $i => $hash) {
            $newIndex = $reservedIndexes[$i];
            $existingHashes->put($hash, $newIndex);

            $bandsToInsert[] = [
                'splitter_id' => $this->splitter->getDetails()->id,
                'band_index' => $newIndex,
                'signature_hash' => $hash,
                'current_value' => 0,
            ];

            foreach ($this->bandParamsMap[$hash] as $paramName => $paramKey) {
                $pivotToInsert[] = [
                    'splitter_id' => $this->splitter->getDetails()->id,
                    'band_index' => $newIndex,
                    'param_id' => $paramMap->get($paramKey),
                    'key_name' => $paramName
                ];
            }
        }

        DB::transaction(static function () use ($bandsToInsert, $pivotToInsert): void {
            DB::table('refract_bands')->insertOrIgnore($bandsToInsert);
            if ($pivotToInsert !== []) {
                DB::table('refract_bands_params')->insertOrIgnore($pivotToInsert);
            }
        });

        return $existingHashes->toArray();
    }

    /**
     * @param int $count
     * @return int[]
     */
    private function reserveIndexes(int $count): array
    {
        return DB::transaction(function () use ($count): array {
            $splitter = DB::table('refract_splitters')
                ->where('id', $this->splitter->getDetails()->id)
                ->lockForUpdate()
                ->first();

            $startIndex = $splitter->bands_count + 1;

            DB::table('refract_splitters')
                ->where('id', $this->splitter->getDetails()->id)
                ->increment('bands_count', $count);

            $result = [];
            for ($index = $startIndex; $index < ($startIndex + $count); $index++) {
                $result[] = $index;
            }
            return $result;
        });
    }


}
