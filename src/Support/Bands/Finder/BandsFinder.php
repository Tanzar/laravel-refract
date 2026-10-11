<?php

namespace Tanzar\Refract\Support\Bands\Finder;

use Override;
use Tanzar\Refract\Exceptions\RefractException;
use Tanzar\Refract\Support\Bands\Tree\BandsBranch;

final class BandsFinder implements BandsFinderInterface
{
    /** @var array<string, string> */
    private array $map = [];

    public function __construct(
        private BandsBranch $root
    ) { }

    #[Override]
    public function where(string $key, string|int|float|bool $value): BandsFinderInterface
    {
        if (!in_array($key, $this->root->allowedKeys())) {
            throw new RefractException("Key '$key' is not allowed, use only loaded keys");
        }

        $this->map[$key] = (string) $value;
        return $this;
    }

    #[Override]
    public function value(): float
    {
        return $this->root->getValue($this->map);
    }

    /**
     * @return int[]
     */
    public function bands(): array
    {
        return $this->root->getBands($this->map);
    }

}
