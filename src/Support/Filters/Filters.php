<?php

namespace Tanzar\Refract\Support\Filters;

use Illuminate\Support\Carbon;
use Override;
use Tanzar\Refract\Models\CachedLens;

final class Filters implements FiltersInterface
{
    /** @var array<string, array{type: string, value: mixed}> */
    private array $values = [];

    #[Override]
    public function date(string $key, Carbon $value): FiltersInterface
    {
        return $this->set($key, $value, 'date');
    }

    #[Override]
    public function int(string $key, int $value): FiltersInterface
    {
        return $this->set($key, $value, 'int');
    }

    #[Override]
    public function float(string $key, float $value): FiltersInterface
    {
        return $this->set($key, $value, 'float');
    }

    #[Override]
    public function string(string $key, string $value): FiltersInterface
    {
        return $this->set($key, $value, 'string');
    }

    #[Override]
    public function bool(string $key, bool $value): FiltersInterface
    {
        return $this->set($key, $value, 'bool');
    }

    private function set(string $key, mixed $value, string $type): self
    {
        $this->values[$key] = [
            'value' => $value,
            'type' => $type
        ];
        return $this;
    }

    public function get(string $key): mixed
    {
        return $this->values[$key]['value'] ?? null;
    }

    /**
     * @return string[]
     */
    public function keys(): array
    {
        $keys = array_keys($this->values);
        sort($keys);
        return $keys;
    }

    public function hash(): string
    {
        $values = [];
        foreach ($this->keys() as $key) {
            $value = $this->values[$key]['value'];
            if ($value instanceof Carbon) {
                $value = $value->format('Y-m-d H:i:s');
            }
            $values[$key] = $value;
        }
        return hash('sha256', http_build_query($values));
    }

    public static function ref(string $key): FiltersRef
    {
        return new FiltersRef($key);
    }

    /**
     * @return array<array{type: string, value: mixed}>
     */
    public function toArray(): array
    {
        $result = [];
        foreach ($this->keys() as $key) {
            $value = $this->values[$key];
            if ($value['type'] === 'date') {
                $value['value'] = $value['value']->format('Y-m-d');
            }
            $result[$key] = $value;
        }
        return $result;
    }

    public static function fromCached(CachedLens $cached): self
    {
        $instance = new Filters();
        $instance->values = $cached->filters;
        return $instance;
    }
}
