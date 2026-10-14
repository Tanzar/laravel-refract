<?php

namespace Tanzar\Refract\Support\Lens\Nodes\Value;

use Override;
use Tanzar\Refract\Support\Bands\Bands;
use Tanzar\Refract\Support\Lens\Nodes\LensRootNode;
use Tanzar\Refract\Support\Lens\Nodes\LensValueNode;

final class LensIntegerNode extends LensValueNode
{
    public function __construct(string $path, Bands $bands, LensRootNode $root, private int $value)
    {
        parent::__construct($path, $bands, $root);
    }

    #[Override]
    public function value(): int
    {
        return $this->value;
    }

}
