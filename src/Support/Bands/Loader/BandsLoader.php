<?php

namespace Tanzar\Refract\Support\Bands\Loader;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Tanzar\Refract\Exceptions\RefractException;
use Tanzar\Refract\Splitter\Splitter;
use Tanzar\Refract\Support\Bands\Bands;
use Tanzar\Refract\Support\Bands\BandsInterface;
use Tanzar\Refract\Support\Bands\Tree\BandsBranch;
use Tanzar\Refract\Support\Filters\Filters;
use Tanzar\Refract\Support\RefractFactory;

class BandsLoader
{
    private Splitter $splitter;

    /** @var Collection<string, LoaderCondition> */
    private Collection $conditions;

    public function __construct(
        string $splitter,
        private Filters $filters
    ) {
        $this->splitter = RefractFactory::splitter($splitter);
        $this->conditions = collect();
    }

    public function where(string $key, mixed $value, string $operator = '='): self
    {
        $this->checkKey($key);

        $this->conditions->put($key, new WhereCondition($this->filters, $key, $value, $operator));
        return $this;
    }

    /**
     * @param string $key
     * @param mixed[] $values
     * @return BandsLoader
     */
    public function whereIn(string $key, array $values): self
    {
        $this->checkKey($key);

        $this->conditions->put($key, new WhereInCondition($this->filters, $key, $values));
        return $this;
    }

    public function whereBetween(string $key, mixed $from, mixed $to): self
    {
        $this->checkKey($key);

        $this->conditions->put($key, new WhereBetweenCondition($this->filters, $key, $from, $to));
        return $this;
    }

    public function load(): BandsInterface
    {
        if ($this->conditions->isEmpty()) {
            throw new RefractException('Loader must have at least 1 condition set');
        }

        $keys = $this->conditions->keys()
            ->sort()
            ->values()
            ->toArray();

        $tree = $this->buildDataTree($keys);

        return new Bands($tree);
    }

    /**
     * @param string[] $keys
     * @return BandsBranch
     */
    private function buildDataTree(array $keys): BandsBranch
    {
        $tree = new BandsBranch(0, $keys);

        $this->indexQuery()
            ->chunk(200, function (Collection $rows) use ($tree) {
                $bandsIndexes = $rows->pluck('band_index')->toArray();

                $this->getBands($bandsIndexes)
                    ->each(function (Collection $bandRows, int $bandIndex) use ($tree) {
                        $first = $bandRows->first();

                        $path = [];
                        $bandRows->each(function ($row) use (&$path) {
                            if (!is_null($row->key_name)) {
                                $path[$row->key_name] = $row->raw_value;
                            }
                        });

                        $tree->add($bandIndex, $first->band_value, $path);
                        
                    });
            });

        return $tree;
    }

    private function indexQuery(): Builder
    {
        $query = DB::table('refract_bands')
            ->select('band_index')
            ->where('splitter_id', $this->splitter->id());
        
        $this->buildFiltersConditions($query);

        return $query->orderBy('band_index');
    }

    private function buildFiltersConditions(Builder $query): void
    {
         /** @var LoaderCondition $condition */
        foreach ($this->conditions as $key => $condition) {
            $query->whereExists(function (Builder $sub) use ($key, $condition) {
                $sub->select(DB::raw(1))
                    ->from('refract_bands_params as rbp_sub')
                    ->join('refract_params as rp_sub', 'rbp_sub.param_id', '=', 'rp_sub.id')
                    ->whereColumn('rbp_sub.band_index', 'refract_bands.band_index')
                    ->where('rbp_sub.splitter_id', $this->splitter->id())
                    ->where('rbp_sub.key_name', $key);

                $column = 'rp_sub.' . $this->splitter->requiredKeys()->getType($key)->column();

                $condition->apply($sub, $column);
            });
        }
    }
    
    /**
     * @param int[] $indexes
     * @return Collection<int, Collection<int, \stdClass>>
     */
    private function getBands(array $indexes): Collection
    {
        return DB::table('refract_bands_params as rbp')
            ->leftJoin('refract_params as rp', 'rp.id', '=', 'rbp.param_id')
            ->leftJoin('refract_bands as rb', function (JoinClause $join) {
                $join->on('rb.band_index', '=', 'rbp.band_index')
                    ->where('rb.splitter_id', '=', $this->splitter->id());
            })
            ->where('rbp.splitter_id', $this->splitter->id())
            ->whereIn('rbp.band_index', $indexes)
            ->select([
                'rbp.band_index',
                'rb.current_value as band_value',
                'rbp.key_name',
                'rp.raw_value'
            ])
            ->get()
            ->groupBy('band_index');
    }

    private function checkKey(string $key): void
    {
        if (!$this->splitter->requiredKeys()->haveKey($key)) {
            throw new RefractException("Key '$key' is not defined in splitter: " . $this->splitter::class);
        }
    }
}
