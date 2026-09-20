<?php

declare(strict_types=1);

use Tanzar\Refract\LaravelRefractServiceProvider;
use Tanzar\Refract\Support\Tracking\TrackingMap;

arch()->preset()->php()->ignoring(TrackingMap::class);

arch()->preset()->security();

arch('it will not use dd(), ddd(), env(), or exit()')
    ->expect(['dd', 'ddd', 'env', 'exit'])
    ->each->not->toBeUsed();

arch('the package source declares strict types')
    ->expect(LaravelRefractServiceProvider::class)
    ->toUseStrictTypes();
