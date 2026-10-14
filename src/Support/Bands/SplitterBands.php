<?php

namespace Tanzar\Refract\Support\Bands;

use Tanzar\Refract\Support\Bands\Finder\BandsFinder;
use Tanzar\Refract\Support\Bands\Finder\BandsFinderInterface;
use Tanzar\Refract\Support\Bands\Tree\BandsBranch;

class SplitterBands implements SplitterBandsInterface
{
    /** @var BandsFinder[] */
    private array $recentFinders = [];

    public function __construct(public BandsBranch $root) { }


    public function find(): BandsFinderInterface
    {
        $finder = new BandsFinder($this->root);

        $this->recentFinders[] = $finder;

        return $finder;
    }

    /**
     * @return int[]
     */
    public function pullRequestedBands(): array
    {
        $bands = [];

        /** @var BandsFinder $finder */
        foreach($this->recentFinders as $finder) {
            $finderBands = $finder->bands();

            $bands = array_values(
                array_unique(
                    array_merge($bands, $finderBands)
                )
            );
        }
        
        $this->recentFinders = [];
        return $bands;
    }
}
