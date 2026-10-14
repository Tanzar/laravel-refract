<?php

namespace Tanzar\Refract\Support\Bands\Loader;

use Tanzar\Refract\Exceptions\RefractException;
use Tanzar\Refract\Support\Bands\Bands;
use Tanzar\Refract\Support\Filters\Filters;

class BandsLoaders
{
    /** @var array<string, BandsLoader> */
    private array $loaders = [];

    public function __construct(private Filters $filters) { }

    public function loader(string $key, string $splitter): BandsLoader
    {
        $instance = new BandsLoader($splitter, $this->filters);

        $this->loaders[$key] = $instance;

        return $instance;
    }

    public function load(): Bands
    {
        if (empty($this->loaders)) {
            throw new RefractException('No loaders were set');
        }

        $bands = new Bands();

        /**
         * @var string $key
         * @var BandsLoader $loader
         */
        foreach ($this->loaders as $key => $loader) {
            $bands->add($key, $loader->load());
        }
        return $bands;
    }
}
