<?php

namespace Tanzar\Refract\Support\Lens\Nodes;

use Illuminate\Support\Str;
use Override;
use Tanzar\Refract\Exceptions\RefractException;
use Tanzar\Refract\Support\Bands\Bands;
use Tanzar\Refract\Support\Lens\Nodes\Collection\LensObjectNode;

final class LensRootNode implements LensRootNodeInterface
{
    private LensObjectNode $root;

    public function __construct(private Bands $bands)
    {
        $this->root = new LensObjectNode('', $bands, $this);
    }

    #[Override]
    public function value(string $path): int|float|string|bool
    {
        $node = $this->get($path);
        if ($node instanceof LensValueNode) {
            return $node->value();
        }
        return '';
    }

    #[Override]
    public function get(string $path): LensNode
    {
        $currentNode = $this->root;
        $keys = Str::of($path)->explode('.');

        $found = null;
        foreach($keys as $key) {
            $child = $currentNode->get($key);
            if (!$child) {
                continue;
            }
            if ($child instanceof LensCollectionNode) {
                $currentNode = $child;
            }
            if ($key === $keys->last()) {
                $found = $child;
            }
        }
        if ($found) {
            return $found;
        }
        throw new RefractException("No node was found for path: '$path'");
    }

    #[Override]
    public function root(): LensObjectNode
    {
        return $this->root;
    }

}
