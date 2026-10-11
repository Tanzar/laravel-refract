<?php

namespace Tanzar\Refract\Support\Bands\Loader;

use Illuminate\Database\Query\Builder;
use Tanzar\Refract\Support\Filters\Filters;

final class WhereCondition extends LoaderCondition
{
    public function __construct(
        Filters $filters,
        string $key,
        private readonly mixed $value,
        private readonly string $operator = '='
    )
    {
        parent::__construct($filters, $key);
    }

    public function apply(Builder $query, string $column): void
    {
        $query->where(
            $column,
            $this->operator,
            $this->getValue($this->value)
        );
    }
}
