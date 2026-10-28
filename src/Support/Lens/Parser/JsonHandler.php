<?php

namespace Tanzar\Refract\Support\Lens\Parser;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class JsonHandler
{
    /** @var Collection<string, mixed> */
    private Collection $nodes;

    public function __construct()
    {
        $this->nodes = collect();
    }

    /**
     * @param string $path
     * @param array<string, mixed> $value
     * @return void
     */
    public function add(string $path, array $value): void
    {
        $pathNodes = Str::of($path)->explode('.');
        $lastIndex = $pathNodes->count() - 1;
        $current = $this->nodes;
        foreach ($pathNodes as $index => $key) {
            if ($index === $lastIndex) {
                $current->put($key, $value);
            } else {
                if (!isset($current[$key])) {
                    $current[$key] = collect();
                }
                $current = $current->get($key);
                
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->nodes->toArray();
    }
}
