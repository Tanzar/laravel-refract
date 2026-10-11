<?php

namespace Tanzar\Refract\Support\Bands\Finder;

interface BandsFinderInterface
{
    public function where(string $key, string|int|float|bool $value): self;

    public function value(): float;
}
