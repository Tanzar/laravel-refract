<?php

namespace Tanzar\Refract\Helpers;

use Tanzar\Refract\Exceptions\RefractException;
use Tanzar\Refract\Services\Optimizer\RefractOptimizer;
use Tanzar\Refract\Splitter\Splitter;

final class RefractHelper
{

    public static function splitter(string $splitterClass): Splitter
    {
        $aliases = RefractConfig::aliases();
        
        $class = $aliases[$splitterClass] ?? $splitterClass;

        if (!is_a($class, Splitter::class, true)) {
            throw new RefractException("Class $class is not a valid Splitter");
        }

        return app($class);
    }

    public static function optimizer(): RefractOptimizer
    {
        return new RefractOptimizer();
    }
}
