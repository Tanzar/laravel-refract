<?php

namespace Tanzar\Refract\Support\Lens\Nodes\Collection;

use Closure;
use Override;
use Tanzar\Refract\Support\Lens\Nodes\LensCollectionNode;
use Tanzar\Refract\Support\Lens\Nodes\LensNode;
use Tanzar\Refract\Support\Lens\Nodes\Value\LensComputedNode;
use Tanzar\Refract\Support\Lens\Nodes\Value\LensFloatNode;
use Tanzar\Refract\Support\Lens\Nodes\Value\LensIntegerNode;
use Tanzar\Refract\Support\Lens\Nodes\Value\LensStringNode;

class LensArrayNode extends LensCollectionNode
{
    private int $count = 0;

    public function int(int $value): LensIntegerNode
    {
        $node = new LensIntegerNode($this->childPath($this->count), $this->bands(), $this->root(), $value);

        $this->addChild($this->count, $node);

        $this->count++;

        return $node;
    }

    public function float(float $value): LensFloatNode
    {
        $node = new LensFloatNode($this->childPath($this->count), $this->bands(), $this->root(), $value);

        $this->addChild($this->count, $node);

        $this->count++;
        
        return $node;
    }

    public function string(string $value): LensStringNode
    {
        $node = new LensStringNode($this->childPath($this->count), $this->bands(), $this->root(), $value);

        $this->addChild($this->count, $node);

        $this->count++;
        
        return $node;
    }

    public function computed(Closure $handle): LensComputedNode
    {
        $node = new LensComputedNode($this->childPath($this->count), $this->bands(), $this->root(), $handle);

        $this->addChild($this->count, $node);

        $this->count++;
        
        return $node;
    }

    public function object(): LensObjectNode
    {
        $node = new LensObjectNode($this->childPath($this->count), $this->bands(), $this->root());

        $this->addChild($this->count, $node);

        $this->count++;
        
        return $node;
    }

    public function array(): LensArrayNode
    {
        $node = new LensArrayNode($this->childPath($this->count), $this->bands(), $this->root());

        $this->addChild($this->count, $node);

        $this->count++;
        
        return $node;
    }

    #[Override]
    public function toArray(): array
    {
        $array = [];
        $this->each(function (string|int $key, LensNode $node) use (&$array) {
            $array[] = $node->toArray();
        });
        return $array;
    }

}
