<?php

namespace Tanzar\Refract\Support\Lens\Nodes;

abstract class LensValueNode extends LensNode
{
    /** @var array<string, int|float|string|bool> */
    private array $options = [];

    abstract public function value(): int|float|string|bool|null;

    final public function option(string $key, int|float|string|bool $value): self
    {
        $this->options[$key] = $value;
        return $this;
    }

    /**
     * @return array{options: array<bool|float|int|string>, path: string, value: bool|float|int|string}
     */
    final public function toArray(): array
    {
        return [
            'value' => $this->value(),
            'path' => $this->path(),
            'options' => $this->options
        ];
    }
}
