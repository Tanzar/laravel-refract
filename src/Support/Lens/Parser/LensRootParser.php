<?php

namespace Tanzar\Refract\Support\Lens\Parser;

use Tanzar\Refract\Models\CachedLens;
use Tanzar\Refract\Support\Lens\Nodes\LensRootNode;

class LensRootParser extends LensParser
{
    public function __construct(CachedLens $cached, private LensRootNode $root)
    {
        parent::__construct($cached);
    }    


    /**
     * @return array<string, mixed>
     */
    public function formatMetaData(): array
    {
        return $this->root->meta()->toArray();
    }

    /**
     * @return array<int|string, mixed>
     */
    public function formatNodes(): array
    {
        return $this->root->root()->toArray();
    }

}
