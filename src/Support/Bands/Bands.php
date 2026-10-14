<?php

namespace Tanzar\Refract\Support\Bands;

use Tanzar\Refract\Exceptions\RefractException;
use Tanzar\Refract\Support\Bands\Finder\BandsFinderInterface;
use Override;

class Bands implements BandsInterface
{
    /** @var array<string, SplitterBandsInterface> */
   private array $bands = [];

   public function add(string $key, SplitterBandsInterface $splitterBands): void
   {
        $this->bands[$key] = $splitterBands;
   }

   #[Override]
   public function for(string $key): BandsFinderInterface
   {
       if (!isset($this->bands[$key])) {
        throw new RefractException("Bands for '$key' are not loaded");
       }

       return $this->bands[$key]->find();
   }

   
}
