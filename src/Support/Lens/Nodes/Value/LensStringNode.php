<?php

namespace Tanzar\Refract\Support\Lens\Nodes\Value;

use Override;
use Tanzar\Refract\Support\Bands\Bands;
use Tanzar\Refract\Support\Lens\Nodes\LensRootNode;
use Tanzar\Refract\Support\Lens\Nodes\LensValueNode;

final class LensStringNode extends LensValueNode
{
    public function __construct(string $path, Bands $bands, LensRootNode $root, private string $value)
    {
        parent::__construct($path, $bands, $root);
    }

    #[Override]
    public function value(): string
    {
        return $this->value;
    }

}
