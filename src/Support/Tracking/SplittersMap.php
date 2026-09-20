<?php

namespace Tanzar\Refract\Support\Tracking;

use Illuminate\Database\Eloquent\Model;
use ReflectionClass;
use Tanzar\Refract\Splitter\Splitter;

final class SplittersMap
{

    /**
     * @param array<class-string<Model>, array<class-string<Splitter>>> $splitters
     */
    public function __construct(private array $splitters = [])
    { }

    /**
     * @param class-string $className
     * @return bool
     */
    public function add(string $className): bool
    {
        $reflection = new ReflectionClass($className);

        if ($reflection->isSubclassOf(Splitter::class) && $reflection->isInstantiable()) {
            /** @var class-string<Splitter> $className */
            $model = $className::modelClass();

            $modelSplitters = $this->splitters[$model] ?? [];

            if (!in_array($className, $modelSplitters)) {
                $modelSplitters[] = $className;
                $this->splitters[$model] = $modelSplitters;
                return true;
            }
        }
        return false;
    }

    public function isTracked(string $model): bool
    {
        return isset($this->splitters[$model]);
    }

    /**
     * @param Model $model
     * @return class-string<Splitter>[]|null
     */
    public function getForModel(Model $model): ?array
    {
        return $this->splitters[$model::class] ?? null;
    }

    /**
     * Summary of toArray
     * @return array<class-string<Model>, array<class-string<Splitter>>>
     */
    public function toArray(): array
    {
        return $this->splitters;
    }
}
