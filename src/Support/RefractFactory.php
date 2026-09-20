<?php

namespace Tanzar\Refract\Support;

use Tanzar\Refract\Config\RefractConfig;
use Tanzar\Refract\Exceptions\RefractException;
use Tanzar\Refract\Splitter\Splitter;
use Tanzar\Refract\Support\Tracking\TrackingMap;

/**
 * Creates instances of required classes
 */
class RefractFactory
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

    public static function trackMap(): TrackingMap
    {
        return new TrackingMap();
    }
}
