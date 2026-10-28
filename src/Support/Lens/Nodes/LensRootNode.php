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
    private LensMetaData $meta;

    public function __construct(private Bands $bands)
    {
        $this->root = new LensObjectNode('', $bands, $this);
        $this->meta = new LensMetaData();
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
        $node = $this->find($path);
        if ($node) {
            return $node;
        }
        throw new RefractException("No node was found for path: '$path'");
    }

    public function have(string $path): bool
    {
        return $this->find($path) !== null;
    }

    private function find(string $path): ?LensNode
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
        return null;
    }

    #[Override]
    public function meta(): LensMetaData
    {
        return $this->meta;
    }

    #[Override]
    public function root(): LensObjectNode
    {
        return $this->root;
    }

    public function eachValueNode(callable $handle): void
    {
        $this->root->each(function (string $key, LensNode $node) use ($handle) {
            if ($node instanceof LensValueNode) {
                $handle($node);
            }
        });
    }

}
