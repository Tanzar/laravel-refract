<?php

namespace Tanzar\Refract\Support\Bands\Tree;

use Tanzar\Refract\Exceptions\RefractException;

class BandsBranch
{
    private float $value = 0.0;
    /** @var array<string, BandsBranch|BandsLeaf> */
    private array $childern = [];
    private string $branchKey;
    private bool $isLastBranchLayer = false;

    /**
     * @param int $index
     * @param string[] $keys
     * @throws RefractException
     */
    public function __construct(private int $index, private array $keys)
    {
        $key = $this->keys[$this->index] ?? null;
        if ($key === null) {
            throw new RefractException("Invalid key index {$this->index} for building branch");
        }
        $this->branchKey = $key;
        $this->isLastBranchLayer = $this->index === (count($this->keys) - 1);
    }

    /**
     * @param int $band
     * @param float $value
     * @param array<string, mixed> $params
     * @throws RefractException
     * @return void
     */
    public function add(int $band, float $value, array $params): void
    {
        $keyValue = $params[$this->branchKey] ?? null;
        if ($keyValue === null) {
            throw new RefractException("Missing key value for branch key {$this->branchKey}");
        }
        unset($params[$this->branchKey]);

        $this->value += $value;

        $child = $this->childern[$keyValue] ?? null;

        if ($child) {
            /** @var BandsBranch|BandsLeaf $child */
            $child->add($band, $value, $params);
        } else {
            if ($this->isLastBranchLayer) {
                $leaf = new BandsLeaf();
                $leaf->add($band, $value);
                $this->childern[$keyValue] = $leaf;
            } else {
                $branch = new BandsBranch($this->index + 1, $this->keys);
                $branch->add($band, $value, $params);
                $this->childern[$keyValue] = $branch;
            }
        }
    }

    /**
     * @param array<string, mixed> $params
     * @return float
     */
    public function getValue(array $params): float
    {
        $paramValue = $params[$this->branchKey] ?? null;
        
        if ($paramValue) {
            unset($params[$this->branchKey]);
            
            $child = $this->childern[$paramValue] ?? null;
            if ($child instanceof BandsLeaf) {
                return $child->getValue();
            } else if ($child instanceof BandsBranch) {
                return $child->getValue($params);   
            }
            return 0.0;
        }
        
        if (empty($params)) {
            return $this->value;
        }
        
        $sum = 0.0;
        /** @var BandsBranch $child */
        foreach ($this->childern as $child) {
            $sum += $child->getValue($params);
        }
        return $sum;
    }

    /**
     * @param array<string, mixed> $params
     * @return int[]
     */
    public function getBands(array $params): array
    {
        $paramValue = $params[$this->branchKey] ?? null;
        
        if ($paramValue) {
            unset($params[$this->branchKey]);
            
            $child = $this->childern[$paramValue] ?? null;
            if ($child instanceof BandsLeaf) {
                return $child->getBands();
            } else if ($child instanceof BandsBranch) {
                return $child->getBands($params);   
            }
            return [];
        }
        
        $bands = [];
        /** @var BandsBranch $child */
        foreach ($this->childern as $child) {
            $childBands = $child->getBands($params);
            $bands = array_values(
                array_unique(
                    array_merge($bands, $childBands)
                )
            );
        }
        return $bands;
    }

    /**
     * @return string[]
     */
    public function allowedKeys(): array
    {
        return $this->keys;
    }
}
