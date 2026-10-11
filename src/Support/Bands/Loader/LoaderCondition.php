<?php

namespace Tanzar\Refract\Support\Bands\Loader;

use Illuminate\Database\Query\Builder;
use Tanzar\Refract\Support\Filters\Filters;
use Tanzar\Refract\Support\Filters\FiltersRef;

abstract class LoaderCondition
{
    public function __construct(
        protected readonly Filters $filters,
        protected readonly string $key
    )
    { }

    abstract public function apply(Builder $query, string $column): void;

    final protected function getValue(mixed $value): mixed
    {
        if ($value instanceof FiltersRef) {
            return $this->filters->get($this->key);
        }
        return $value;
    }
}
