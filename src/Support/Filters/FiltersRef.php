<?php

namespace Tanzar\Refract\Support\Filters;

final class FiltersRef
{
    public function __construct(private string $key) {}

    public function key(): string
    {
        return $this->key;
    }
}
