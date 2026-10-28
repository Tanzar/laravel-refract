<?php

namespace Workbench\App\Lens;

use Tanzar\Refract\Support\Bands\Loader\BandsLoaders;
use Override;
use Tanzar\Refract\Support\Bands\Bands;
use Tanzar\Refract\Support\Filters\Filters;
use Tanzar\Refract\Support\Lens\Lens;
use Tanzar\Refract\Support\Lens\Nodes\LensRootNode;
use Tanzar\Refract\Support\Lens\Nodes\LensRootNodeInterface;
use Workbench\App\Splitters\TotalFoodsSplitter;

class TotalFoodsLens extends Lens
{
    #[Override]
    protected function load(BandsLoaders $loaders): void
    {
        $loaders->loader('foods', TotalFoodsSplitter::class)
            ->where('category', 'fast_food')
            ->whereBetween('price', 10, Filters::ref('max'));
    }

    #[Override]
    protected function format(LensRootNode $node): void
    {
        $node->root()->computed('cheap', function (Bands $bands, LensRootNodeInterface $root) {
            return $bands->for('foods')->where('price', 13)->value();
        });

        $node->root()->computed('expensive', function (Bands $bands, LensRootNodeInterface $root) {
            return $bands->for('foods')->where('price', 15)->value();
        });
    }

}
