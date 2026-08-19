<?php

declare(strict_types=1);

namespace Tanzar\Refract\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Tanzar\Refract\Refract
 */
class Refract extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Tanzar\Refract\Refract::class;
    }
}
