<?php

namespace Tanzar\Refract\Helpers;

use Tanzar\Refract\Splitter\Splitter;

final class RefractConfig
{
    public static function precision(): int
    {
        return (int) config('refract.precision', 4);
    }

    public static function discoverNamespace(): string
    {
        return config('refract.discovery.namespace', 'App\\');
    }

    public static function discoverPath(): string
    {
        return config('refract.discovery.path', app_path());
    }

    /**
     * @return array<string, class-string<Splitter>>
     */
    public static function aliases(): array
    {
        return config('refract.splitters.aliases', []);
    }
}
