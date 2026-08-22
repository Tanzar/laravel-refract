<?php

declare(strict_types=1);

namespace Tanzar\Refract\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Tanzar\Refract\LaravelRefractServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LaravelRefractServiceProvider::class,
        ];
    }
    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom([
            __DIR__ . '/../database/migrations',
            __DIR__ . '../../workbench/database/migrations'
        ]);
    }
}
