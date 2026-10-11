<?php

namespace Tanzar\Refract\Support\Filters;

use Illuminate\Support\Carbon;

interface FiltersInterface
{
    public function int(string $key, int $value): self;
    
    public function float(string $key, float $value): self;
    
    public function string(string $key, string $value): self;
    
    public function bool(string $key, bool $value): self;
    
    public function date(string $key, Carbon $value): self;
}
