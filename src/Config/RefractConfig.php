<?php

namespace Tanzar\Refract\Config;

use Tanzar\Refract\Splitter\Splitter;
use Tanzar\Refract\Support\Lens\Lens;

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
     * @return array<string, class-string<Splitter|Lens>>
     */
    public static function aliases(string $type): array
    {
        return match($type) {
            'splitter' => config('refract.splitters.aliases', []),
            'lens' => config('refract.lens.aliases', []),
            default => []
        };
    }

    public static function splittersDefaultQueue(): string
    {
        return config('refract.splitters.queue', 'default');
    }

    public static function bufferDelay(): int
    {
        return config('refract.buffer_delay', 10);
    }

    public static function lensGetAttempts(): int
    {
        return (int) config('refract.lens.wait.attempts', 4);
    }

    public static function lensGetRetryDelay(): int
    {
        return (int) config('refract.lens.wait.delay', 1);
    }
}
