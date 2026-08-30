<?php

namespace Tanzar\Refract\Splitter;

use Illuminate\Support\Carbon;
use Override;
use Tanzar\Refract\Enums\ParamTypes;
use Tanzar\Refract\Exceptions\RefractException;

class SplitterParams implements SplitterParamsInterface
{
    /** @var array<string, array{type: ParamTypes, value: mixed}> */
    private array $params = [];
    private ?string $hash = null;

    public function __construct(
        private RequiredParams $keys,
        private float $modelValue,
        private mixed $modelId
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

    public function getModelId(): mixed
    {
        return $this->modelId;
    }

    /**
     * @return string[]
     */
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

    public function hash(): string
    {
        if ($this->hash !== null) {
            return $this->hash;
        }

        $hashData = [];
        foreach ($this->keys->getKeys() as $key) {
            $hashData[$key] = $this->getValue($key);
        }
        $this->hash = md5(http_build_query($hashData));
        return $this->hash;
    }

    /**
     * @param string $key
     * @return array<string, mixed>
     */
    public function toInsert(string $key): array
    {
        $insert = [
            'type' => $this->getType($key)->value,
            'raw_value' => $this->getValue($key)
        ];

        $valueType = $this->getType($key);
        $value = $this->getValue($key);
        foreach (ParamTypes::cases() as $type) {
            $column = $type->column();
            $insert[$column] = $type === $valueType ? $value : null;
        }
        return $insert;
    }

    
}
