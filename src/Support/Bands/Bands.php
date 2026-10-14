<?php

namespace Tanzar\Refract\Support\Bands;

use Tanzar\Refract\Exceptions\RefractException;
use Tanzar\Refract\Support\Bands\Finder\BandsFinderInterface;
use Override;
use Tanzar\Refract\Splitter\Splitter;

class Bands implements BandsInterface
{
    /** @var array<string, SplitterBands> */
    private array $bands = [];
    /** @var array<string, Splitter> */
    private array $splitters = [];

    public function add(string $key, Splitter $splitter, SplitterBands $splitterBands): void
    {
        $this->bands[$key] = $splitterBands;
        $this->splitters[$key] = $splitter;
    }

    #[Override]
    public function for(string $key): BandsFinderInterface
    {
       if (!isset($this->bands[$key])) {
        throw new RefractException("Bands for '$key' are not loaded");
       }

       return $this->bands[$key]->find();
    }

    /**
     * @return array<int, int[]>
     */
    public function pullRequestedBands(): array
    {
        $requested = [];

        foreach ($this->bands as $key => $splitterBands) {
            $splitter = $this->splitters[$key];
            $bands = $requested[$splitter->id()] ?? [];
            $requested[$splitter->id()] = array_values(
                array_unique(
                    array_merge($bands, $splitterBands->pullRequestedBands())
                )
            );
        }

        return $requested;
    }
}
