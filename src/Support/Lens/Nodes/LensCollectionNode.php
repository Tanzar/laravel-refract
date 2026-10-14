<?php

namespace Tanzar\Refract\Support\Lens\Nodes;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Tanzar\Refract\Exceptions\RefractException;
use Tanzar\Refract\Support\Bands\Bands;

abstract class LensCollectionNode extends LensNode
{
    /** @var Collection<string|int, LensNode> */
    private Collection $nodes;

    public function __construct(string $path, Bands $bands,LensRootNode $root)
    {
        $this->nodes = collect();
        parent::__construct($path, $bands, $root);
    }

    final protected function addChild(string|int $key, LensNode $node): void
    {
        if (is_string($key) && Str::of($key)->contains('.')) {
            throw new RefractException('Node keys cannot contain dot (.) character');
        }
        $this->nodes->put($key, $node);
    }

    final protected function childPath(string|int $key): string
    {
        $path = $this->path();
        $path .= $path === '' ? '' : '.';
        $path .= $key;
        return $path;
    }

    final public function get(string|int $key): ?LensNode
    {
        /** @var LensNode|null $node */
        $node = $this->nodes->get($key);
        return $node;
    }

    final public function each(callable $handle): void
    {
        foreach ($this->nodes as $key => $node) {
            $handle($key, $node);
        }
    }

}
