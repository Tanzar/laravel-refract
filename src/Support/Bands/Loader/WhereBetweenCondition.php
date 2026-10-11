<?php

namespace Tanzar\Refract\Support\Bands\Loader;

use Illuminate\Database\Query\Builder;
use Tanzar\Refract\Support\Filters\Filters;

class WhereBetweenCondition extends LoaderCondition
{
    public function __construct(
        Filters $filters,
        string $key,
        private readonly mixed $min,
        private readonly mixed $max
    )
    {
        parent::__construct($filters, $key);
    }

    public function apply(Builder $query, string $column): void
    {
        $query->whereBetween(
            $column,
            [
                $this->getValue($this->min),
                $this->getValue($this->max)
            ]
        );
    }
}
