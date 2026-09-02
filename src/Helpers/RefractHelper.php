<?php

namespace Tanzar\Refract\Helpers;

use Tanzar\Refract\Exceptions\RefractException;
use Tanzar\Refract\Splitter\Splitter;

class RefractHelper
{

    public static function precision(): int
    {
        return (int) config('refract.precision', 4);
    }

    public static function splitter(string $splitterClass): Splitter
    {
        $aliases = config('refract.splitters.aliases', []);
        
        $class = $aliases[$splitterClass] ?? $splitterClass;

        if (!is_string($class) || !is_a($class, Splitter::class, true)) {
            throw new RefractException("Class $class is not a valid Splitter");
        }

        return app($class);
    }
}
