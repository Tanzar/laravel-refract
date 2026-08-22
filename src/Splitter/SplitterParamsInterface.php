<?php

namespace Tanzar\Refract\Splitter;

use Illuminate\Support\Carbon;

interface SplitterParamsInterface
{
    public function date(string $name, Carbon $value): self;

    public function int(string $name, int $value): self;

    public function float(string $name, float $value): self;

    public function string(string $name, string $value): self;

    public function bool(string $name, bool $value): self;
}
