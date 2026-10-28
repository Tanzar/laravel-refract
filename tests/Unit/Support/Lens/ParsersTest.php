<?php

use Tanzar\Refract\Models\CachedLens;
use Tanzar\Refract\Support\Bands\Bands;
use Tanzar\Refract\Support\Bands\SplitterBands;
use Tanzar\Refract\Support\Bands\Tree\BandsBranch;
use Tanzar\Refract\Support\Lens\Nodes\LensRootNode;
use Tanzar\Refract\Support\Lens\Parser\CachedLensParser;
use Tanzar\Refract\Support\Lens\Parser\JsonHandler;
use Tanzar\Refract\Support\Lens\Parser\LensRootParser;
use Workbench\App\Splitters\TotalFoodsSplitter;
use Workbench\Database\Seeders\LensSeeder;

test('json handler returns correct array', function () {
    $handler = new JsonHandler();

    $handler->add('papa.son.toy', [ 'horse', 'train', 'car' ]);
    $handler->add('mom', [ 1, 2, 3 ]);

    expect($handler->toArray())->toBe([
        'papa' => [
            'son' => [
                'toy' => [ 'horse', 'train', 'car' ]
            ]
        ],
        'mom' => [ 1, 2, 3 ]
    ]);
});

test('LensRoot parser returns correct format', function () {

    $bandsRoot = new BandsBranch(0, [ 'type' ]);

    $bands = new Bands();
    $bands->add('main', new TotalFoodsSplitter(), new SplitterBands($bandsRoot));
    $root = new LensRootNode($bands);
    $obj = $root->root()->object('obj');
    $obj->int('val', 12)->option('opt', 1);
    $obj->string('name', 'test');
    $root->root()->float('map', 12.4);
    $root->meta()->set('hello', 'meta');
   
    $cahced = new CachedLens();
    $cahced->id = 10;

    $parser = new LensRootParser($cahced, $root);

    expect($parser->parse())->toBe([
        'lens' => [ 'cacheId' => 10, ],
        'meta' => [ 'hello' => 'meta' ],
        'nodes' => [
            'obj' => [
                'val' => [ 'value' => 12, 'options' => [ 'opt' => 1 ] ],
                'name' => [ 'value' => 'test', 'options' => [] ]
            ],
            'map' => [ 'value' => 12.4, 'options' => [] ],
        ]
    ]);
});

test('CahcedLensPraser returns correct format', function () {
    $this->seed(LensSeeder::class);

    $parser = new CachedLensParser(CachedLens::first());
    expect($parser->parse())->toBe([
        'lens' => [ 'cacheId' => 1, ],
        'meta' => [],
        'nodes' => [
            'cheap' => [ 'value' => 1, 'options' => [] ],
            'expensive' => [ 'value' => 2, 'options' => [] ],
        ]
    ]);
});