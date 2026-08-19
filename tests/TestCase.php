<?php

declare(strict_types=1);

namespace Tanzar\Refract\Tests;

use Tanzar\Refract\LaravelRefractServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LaravelRefractServiceProvider::class,
        ];
    }
}
