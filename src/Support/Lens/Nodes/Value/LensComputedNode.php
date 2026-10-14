<?php

namespace Tanzar\Refract\Support\Lens\Nodes\Value;

use Closure;
use Override;
use Tanzar\Refract\Exceptions\RefractException;
use Tanzar\Refract\Support\Bands\Bands;
use Tanzar\Refract\Support\Lens\Nodes\LensRootNode;
use Tanzar\Refract\Support\Lens\Nodes\LensValueNode;

final class LensComputedNode extends LensValueNode
{
    /** @var array<int, int[]> */
    private array $bandsIndexes = [];
    private bool $locked = false;

    public function __construct(
        string $path,
        Bands $bands,
        LensRootNode $root,
        private Closure $handle
    )
    {
        parent::__construct($path, $bands, $root);
    }

    #[Override]
    public function value(): int|float|string|bool
    {
        if ($this->locked) {
            throw new RefractException("Node '" . $this->path() . "' is locked, by another node");
        }
        $this->locked = true;
        $handle = $this->handle;
        $value = $handle($this->bands(), $this->root());
        if ($this->isNotCorrectType($value)) {
            throw new RefractException("Computed node '" . $this->path() . "' handle return value must be type int|float|string|bool");
        }
        $this->bandsIndexes = $this->bands()->pullRequestedBands();
        $this->locked = false;
        return $value;
    }

    private function isNotCorrectType(mixed $value): bool
    {
        return !(
            is_string($value) ||
            is_int($value) ||
            is_float($value) ||
            is_bool($value)
        );
    }

    /**
     * Summary of bandsIndexes
     * @return array<int, int[]>
     */
    public function bandsIndexes(): array
    {
        return $this->bandsIndexes;
    }

}
