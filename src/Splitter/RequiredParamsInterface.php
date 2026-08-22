<?php

namespace Tanzar\Refract\Splitter;

use Illuminate\Support\Carbon;

interface RequiredParamsInterface
{
    public function date(string $name, Carbon $default = new Carbon()): self;

    public function int(string $name, int $default = 0): self;

    public function float(string $name, float $default = 0.0): self;

    public function string(string $name, string $default = ''): self;

    public function bool(string $name, bool $default = false): self;
}
