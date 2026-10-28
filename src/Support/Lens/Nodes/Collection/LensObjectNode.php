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

final class LensObjectNode extends LensCollectionNode
{
    /** @var array<string, boolean> */
    private array $keys = [];

    public function int(string $key, int $value): LensIntegerNode
    {
        $node = new LensIntegerNode($this->childPath($key), $this->bands(), $this->root(), $value);

        $this->addChild($key, $node);
        $this->keys[$key] = true;
        return $node;
    }

    public function float(string $key, float $value): LensFloatNode
    {
        $node = new LensFloatNode($this->childPath($key), $this->bands(), $this->root(), $value);

        $this->addChild($key, $node);
        $this->keys[$key] = true;
        return $node;
    }

    public function string(string $key, string $value): LensStringNode
    {
        $node = new LensStringNode($this->childPath($key), $this->bands(), $this->root(), $value);

        $this->addChild($key, $node);
        $this->keys[$key] = true;
        return $node;
    }

    public function computed(string $key, Closure $handle): LensComputedNode
    {
        $node = new LensComputedNode($this->childPath($key), $this->bands(), $this->root(), $handle);

        $this->addChild($key, $node);
        $this->keys[$key] = true;
        return $node;
    }

    public function object(string $key): LensObjectNode
    {
        $node = new LensObjectNode($this->childPath($key), $this->bands(), $this->root());

        $this->addChild($key, $node);
        $this->keys[$key] = true;
        return $node;
    }

    public function array(string $key): LensArrayNode
    {
        $node = new LensArrayNode($this->childPath($key), $this->bands(), $this->root());

        $this->addChild($key, $node);
        $this->keys[$key] = true;
        return $node;
    }

    #[Override]
    public function toArray(): array
    {
        $array = [];
        foreach ($this->keys as $key => $state) {
            $node = $this->get($key);
            if ($node) {
                $array[$key] = $node->toArray();
            }
        }
        return $array;
    }

}
