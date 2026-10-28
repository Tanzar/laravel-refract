<?php

namespace Tanzar\Refract\Support\Lens;

use Tanzar\Refract\Support\Bands\Loader\BandsLoaders;
use Tanzar\Refract\Support\Filters\Filters;
use Tanzar\Refract\Support\Lens\Nodes\LensRootNode;

abstract class Lens
{
    final public function generate(Filters $filters): LensRootNode
    {
        $loaders = new BandsLoaders($filters);
        $this->load($loaders);
        $root = new LensRootNode($loaders->load());
        $this->format($root);
        return $root;
    }

    abstract protected function load(BandsLoaders $loaders): void;

    abstract protected function format(LensRootNode $node): void;
}
