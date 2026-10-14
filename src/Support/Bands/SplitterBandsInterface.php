<?php

namespace Tanzar\Refract\Support\Bands;

use Tanzar\Refract\Support\Bands\Finder\BandsFinderInterface;

interface SplitterBandsInterface
{
    public function find(): BandsFinderInterface;
}
