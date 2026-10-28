<?php

namespace Tanzar\Refract\Support\Lens\Parser;

use Tanzar\Refract\Models\CachedLens;

abstract class LensParser
{
    public function __construct(protected readonly CachedLens $cached) { }

    /**
     * @return array{lens: array{cacheId: int}, meta: array<string, mixed>, nodes: array<string, mixed>}
     */
    final public function parse(): array
    {
        return [
            'lens' => [
                'cacheId' => $this->cached->id
            ],
            'meta' => $this->formatMetaData(),
            'nodes' => $this->formatNodes(),
        ];
    }

    /** 
     * @return array<string, mixed>
     */
    abstract public function formatMetaData(): array;

    /** 
     * @return array<string, mixed>
     */
    abstract public function formatNodes(): array;
}
