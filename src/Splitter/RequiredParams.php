<?php

namespace Tanzar\Refract\Splitter;

use Illuminate\Support\Carbon;
use Override;
use Tanzar\Refract\Enums\ParamTypes;

class RequiredParams implements RequiredParamsInterface
{
    private array $keys = [];

    #[Override]
    public function date(string $name, Carbon $default = new Carbon()): RequiredParamsInterface
    {
        $this->setValue($name, ParamTypes::DATE, $default->format('Y-m-d'));
        return $this;
    }

    #[Override]
    public function int(string $name, int $default = 0): RequiredParamsInterface
    {
        $this->setValue($name, ParamTypes::INTEGER, $default);
        return $this;
    }

    #[Override]
    public function float(string $name, float $default = 0.0): RequiredParamsInterface
    {
        $this->setValue($name, ParamTypes::FLOAT, $default);
        return $this;
    }

    #[Override]
    public function string(string $name, string $default = ''): RequiredParamsInterface
    {
        $this->setValue($name, ParamTypes::STRING, $default);
        return $this;
    }

    #[Override]
    public function bool(string $name, bool $default = false): RequiredParamsInterface
    {
        $this->setValue($name, ParamTypes::BOOLEAN, $default);
        return $this;
    }

    private function setValue(string $name, ParamTypes $type, mixed $value): void
    {
        $this->keys[$name] = [ 'type' => $type, 'value' => $value ];
    }

    public function have(string $name, ParamTypes $type): bool
    {
        return isset($this->keys[$name]) && $this->keys[$name]['type'] === $type;
    }

    public function getType(string $name): ?ParamTypes
    {
        return $this->keys[$name]['type'] ?? null;
    }

    public function getDefault(string $name): mixed
    {
        return $this->keys[$name]['value'] ?? null;
    }

    public function getKeys(): array
    {
        return array_keys($this->keys);
    }

    public function encode(): string
    {
        $code = '';

        foreach ($this->keys as $name => $data) {
            $type = $data['type']->value;
            $value = $data['value'];
            $code .= "$name:$type:$value;";
        }
        return $code;
    }
}
