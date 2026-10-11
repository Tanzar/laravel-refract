<?php

namespace Tanzar\Refract\Support\Bands\Loader;

use Illuminate\Database\Query\Builder;
use Tanzar\Refract\Support\Filters\Filters;

final class WhereInCondition extends LoaderCondition
{
    public function __construct(
        Filters $filters,
        string $key,
        private readonly mixed $value
    )
    {
        parent::__construct($filters, $key);
    }

    public function apply(Builder $query, string $column): void
    {
        $query->whereIn(
            $column,
            $this->getValue($this->value)
        );
    }
}
