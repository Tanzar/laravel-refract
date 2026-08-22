<?php

namespace Tanzar\Refract\Splitter;

use Illuminate\Support\Carbon;
use Override;
use Tanzar\Refract\Enums\ParamTypes;
use Tanzar\Refract\Exceptions\RefractException;

class SplitterParams implements SplitterParamsInterface
{
    private array $params = [];

    public function __construct(
        private RequiredParams $keys,
        private float $modelValue
    ) { }

    #[Override]
    public function date(string $name, Carbon $value): SplitterParamsInterface
    {
        $this->setValue($name, ParamTypes::DATE, $value->format('Y-m-d'));
        return $this;
    }

    #[Override]
    public function int(string $name, int $value): SplitterParamsInterface
    {
        $this->setValue($name, ParamTypes::INTEGER, $value);
        return $this;
    }

    #[Override]
    public function float(string $name, float $value): SplitterParamsInterface
    {
        $this->setValue($name, ParamTypes::FLOAT, $value);
        return $this;
    }

    #[Override]
    public function string(string $name, string $value): SplitterParamsInterface
    {
        $this->setValue($name, ParamTypes::STRING, $value);
        return $this;
    }

    #[Override]
    public function bool(string $name, bool $value): SplitterParamsInterface
    {
        $this->setValue($name, ParamTypes::BOOLEAN, $value);
        return $this;
    }

    private function setValue(string $name, ParamTypes $type, mixed $value): void
    {
        if (!$this->keys->have($name, $type)) {
            throw new RefractException("Key $name not allowed, use correct type or add it to required");
        }

        $this->params[$name] = [ 'type' => $type, 'value' => $value ];
    }

    public function getModelValue(): float
    {
        return $this->modelValue;
    }

    public function getKeys(): array
    {
        return $this->keys->getKeys();
    }

    public function getType(string $name): ?ParamTypes
    {
        return $this->params[$name]['type'] ??
            $this->keys->getType($name);
    }

    public function getValue(string $name): mixed
    {
        return $this->params[$name]['value'] ??
            $this->keys->getDefault($name);
    }
}
