<?php

namespace Tanzar\Refract\Support\Lens\Nodes\Value;

use Override;
use Tanzar\Refract\Support\Bands\Bands;
use Tanzar\Refract\Support\Lens\Nodes\LensRootNode;
use Tanzar\Refract\Support\Lens\Nodes\LensValueNode;

final class LensFloatNode extends LensValueNode
{
    public function __construct(string $path, Bands $bands, LensRootNode $root, private float $value)
    {
        parent::__construct($path, $bands, $root);
    }
    
    #[Override]
    public function value(): float
    {
        return $this->value;
    }

}
