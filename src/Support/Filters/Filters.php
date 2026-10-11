<?php

namespace Tanzar\Refract\Support\Filters;

use Illuminate\Support\Carbon;
use Override;

final class Filters implements FiltersInterface
{
    /** @var array<string, mixed> */
    private array $values = [];

    #[Override]
    public function date(string $key, Carbon $value): FiltersInterface
    {
        return $this->set($key, $value);
    }

    #[Override]
    public function int(string $key, int $value): FiltersInterface
    {
        return $this->set($key, $value);
    }

    #[Override]
    public function float(string $key, float $value): FiltersInterface
    {
        return $this->set($key, $value);
    }

    #[Override]
    public function string(string $key, string $value): FiltersInterface
    {
        return $this->set($key, $value);
    }

    #[Override]
    public function bool(string $key, bool $value): FiltersInterface
    {
        return $this->set($key, $value);
    }

    private function set(string $key, mixed $value): self
    {
        $this->values[$key] = $value;
        return $this;
    }

    public function get(string $key): mixed
    {
        return $this->values[$key] ?? null;
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
            $value = $this->values[$key];
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
}
