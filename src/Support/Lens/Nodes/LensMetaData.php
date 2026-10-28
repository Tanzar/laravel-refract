<?php

namespace Tanzar\Refract\Support\Lens\Nodes;

use Illuminate\Support\Collection;

final class LensMetaData
{
    /** @var Collection<string, mixed> */
    private Collection $data;
    
    public function __construct()
    {
        $this->data = collect();
    }

    public function set(string $key, mixed $value): self
    {
        $this->data->put($key, $value);
        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data->toArray();
    }
    
}
