<?php

namespace Tanzar\Refract\Support;

use Tanzar\Refract\Config\RefractConfig;
use Tanzar\Refract\Exceptions\RefractException;
use Tanzar\Refract\Models\CachedLens;
use Tanzar\Refract\Splitter\Splitter;
use Tanzar\Refract\Support\Lens\Lens;
use Tanzar\Refract\Support\Lens\LensSynchronizer;
use Tanzar\Refract\Support\Tracking\TrackingMap;

/**
 * Creates instances of required classes
 */
class RefractFactory
{
    public static function splitter(string $splitterClass): Splitter
    {
        $aliases = RefractConfig::aliases('splitter');
        
        $class = $aliases[$splitterClass] ?? $splitterClass;

        if (!is_a($class, Splitter::class, true)) {
            throw new RefractException("Class $class is not a valid Splitter");
        }

        return app($class);
    }

    public static function lens(string $lens): Lens
    {
        $aliases = RefractConfig::aliases('lens');
        
        $class = $aliases[$lens] ?? $lens;

        if (!is_a($class, Lens::class, true)) {
            throw new RefractException("Class $class is not a valid Lens");
        }

        return app($class);
    }

    public static function trackMap(): TrackingMap
    {
        return new TrackingMap();
    }

    public static function synchronizer(CachedLens $cached): LensSynchronizer
    {
        return app(LensSynchronizer::class, ['cacheId' => $cached->id]);
    }
}
