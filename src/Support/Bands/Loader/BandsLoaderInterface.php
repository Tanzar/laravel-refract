<?php

namespace Tanzar\Refract\Support\Bands\Loader;

interface BandsLoaderInterface
{
    public function where(string $key, mixed $value, string $operator = '='): BandsLoaderInterface;

    /**
     * @param string $key
     * @param mixed[] $values
     * @return BandsLoaderInterface
     */
    public function whereIn(string $key, array $values): BandsLoaderInterface;

    public function whereBetween(string $key, mixed $from, mixed $to): BandsLoaderInterface;
}
