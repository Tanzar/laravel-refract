<?php

namespace Tanzar\Refract\Support\Lens\Nodes;

use Tanzar\Refract\Support\Lens\Nodes\Collection\LensObjectNode;

interface LensRootNodeInterface
{
    public function get(string $path): LensNode;

    public function value(string $path): int|float|string|bool;

    public function root(): LensObjectNode;
}
