<?php

namespace Tanzar\Refract\Support\Lens;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Tanzar\Refract\Support\Lens\Nodes\LensRootNode;
use Tanzar\Refract\Support\Lens\Nodes\LensValueNode;
use Tanzar\Refract\Support\Lens\Nodes\Value\LensComputedNode;

class LensSynchronizer
{
    /** @var Collection<string, array{cached_lens_id: int, path: string, path_value: string}> */
    private Collection $paths;

    /** @var Collection<string, array{cached_lens_id: int, path: string, splitter_id: int, band_index: int}> */
    private Collection $bands;

    /** @var Collection<int, Builder> */
    private Collection $deletes;

    public function __construct(private int $cacheId) {}

    public function sync(LensRootNode $root): void
    {
        $this->paths = collect();
        $this->bands = collect();
        $this->deletes = collect();

        $root->eachValueNode(function (LensValueNode $node) {
            $this->upsertPath($node);

            if ($node instanceof LensComputedNode) {
                foreach ($node->bandsIndexes() as $splitter => $indexes) {
                    foreach ($indexes as $band) {
                        $this->insertBand($node->path(), $splitter, $band);
                    }
                }
            }
        });
        
        $this->perpareBandsDeletes();
        $this->preparePathsDeletes();
        DB::transaction(fn ()  => $this->persist());
    }

    private function upsertPath(LensValueNode $node): void
    {
        $this->paths->put($node->path(), [
            'cached_lens_id' => $this->cacheId,
            'path' => $node->path(),
            'path_value' => (string) json_encode($node->toArray())
        ]);
    }

    public function insertBand(string $path, int $spliter, int $band): void
    {
        $this->bands->put("$path;$spliter;$band", [
            'cached_lens_id' => $this->cacheId,
            'path' => $path,
            'splitter_id' => $spliter,
            'band_index' => $band
        ]);
    }

    private function perpareBandsDeletes(): void
    {
        $toDelete = [];

        DB::table('refract_lens_bands')
            ->where('cached_lens_id', $this->cacheId)
            ->orderBy('path')
            ->lazy()
            ->each(function (\stdClass $row) use (&$toDelete) {
                $key = "$row->path;$row->splitter_id;$row->band_index";
                if (!$this->bands->has($key)) {
                    $toDelete[] = [
                        'cached_lens_id' => $this->cacheId,
                        'path' => $row->path,
                        'splitter_id' => $row->splitter_id,
                        'band_index' => $row->band_index
                    ];
                }
            });

        if (empty($toDelete)) {
            return;
        }

        $this->addToDeletes('refract_lens_bands', $toDelete);
    }

    private function preparePathsDeletes(): void
    {
        $toDelete = [];

        DB::table('refract_lens_paths')
            ->where('cached_lens_id', $this->cacheId)
            ->orderBy('path')
            ->lazy()
            ->each(function (\stdClass $row) use (&$toDelete) {
                if (!$this->paths->has($row->path)) {
                    $toDelete[] = [
                        'cached_lens_id' => $this->cacheId,
                        'path' => $row->path
                    ];
                }
            });

        if (empty($toDelete)) {
            return;
        }

        $this->addToDeletes('refract_lens_paths', $toDelete);
    }

    private function persist(): void
    {
        if ($this->deletes->isNotEmpty()) {
            foreach ($this->deletes as $q) {
                $q->delete();
            }
        }
        
        if ($this->paths->isNotEmpty()) {
            DB::table('refract_lens_paths')
                ->upsert(
                    $this->paths->toArray(),
                    [ 'cached_lens_id', 'path' ],
                    [ 'path_value' ]
                );
        }

        if ($this->bands->isNotEmpty()) {
            DB::table('refract_lens_bands')
                ->insertOrIgnore($this->bands->toArray());
        }
    }

    /**
     * @param string $table
     * @param array<int, array<string, mixed>> $toDelete
     * @return void
     */
    private function addToDeletes(string $table, array $toDelete): void
    {
        
        foreach (array_chunk($toDelete, 50) as $chunk) {
            $q = DB::table($table);
            foreach ($chunk as $delete) {
                $q->orWhere(function (Builder $q) use ($delete) {
                    foreach ($delete as $column => $value) {
                        $q->where($column, '=', $value);
                    }
                });
            }
            $this->deletes->add($q);
        }
    }

}
