<?php

namespace Tanzar\Refract\Config;

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

    public static function splittersDefaultQueue(): string
    {
        return config('refract.splitters.queue', 'default');
    }

    public static function bufferDelay(): int
    {
        return config('refract.buffer_delay', 10);
    }
}
