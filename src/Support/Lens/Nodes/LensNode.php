<?php

namespace Tanzar\Refract\Support\Lens\Nodes;

use Tanzar\Refract\Support\Bands\Bands;

abstract class LensNode
{
    public function __construct(
        private string $path,
        private Bands $bands,
        private LensRootNode $root
    )
    { }

    final public function path(): string
    {
        return $this->path;
    }

    final protected function bands(): Bands
    {
        return $this->bands;
    }

    final protected function root(): LensRootNode
    {
        return $this->root;
    }

    /**
     * @return array<string|int, mixed>
     */
    abstract public function toArray(): array;
}
