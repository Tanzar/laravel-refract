<?php

declare(strict_types=1);

use Tanzar\Refract\LaravelRefractServiceProvider;
use Tanzar\Refract\Services\Optimizer\RefractOptimizer;

arch()->preset()->php()->ignoring(RefractOptimizer::class);

arch()->preset()->security();

arch('it will not use dd(), ddd(), env(), or exit()')
    ->expect(['dd', 'ddd', 'env', 'exit'])
    ->each->not->toBeUsed();

arch('the package source declares strict types')
    ->expect(LaravelRefractServiceProvider::class)
    ->toUseStrictTypes();
