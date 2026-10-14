<?php

namespace Tanzar\Refract\Support\Bands;

use Tanzar\Refract\Support\Bands\Finder\BandsFinderInterface;

interface BandsInterface
{
    public function for(string $splitter): BandsFinderInterface;
}
