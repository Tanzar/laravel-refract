<?php

use Tanzar\Refract\Exceptions\RefractException;
use Tanzar\Refract\Support\Bands\Bands;
use Tanzar\Refract\Support\Bands\SplitterBands;
use Tanzar\Refract\Support\Bands\Tree\BandsBranch;
use Tanzar\Refract\Support\Lens\Nodes\Collection\LensArrayNode;
use Tanzar\Refract\Support\Lens\Nodes\Collection\LensObjectNode;
use Tanzar\Refract\Support\Lens\Nodes\LensMetaData;
use Tanzar\Refract\Support\Lens\Nodes\LensRootNode;
use Tanzar\Refract\Support\Lens\Nodes\LensRootNodeInterface;
use Tanzar\Refract\Support\Lens\Nodes\LensValueNode;
use Tanzar\Refract\Support\Lens\Nodes\Value\LensComputedNode;
use Tanzar\Refract\Support\Lens\Nodes\Value\LensFloatNode;
use Tanzar\Refract\Support\Lens\Nodes\Value\LensIntegerNode;
use Tanzar\Refract\Support\Lens\Nodes\Value\LensStringNode;
use Workbench\App\Splitters\TotalFoodsSplitter;

test('simple value nodes return correct values', function () {
    $bands = new Bands();
    $root = new LensRootNode($bands);

    $int = new LensIntegerNode('int', $bands, $root, 12);
    $int->option('int', 1)
        ->option('float', 12.2)
        ->option('str', 'nope')
        ->option('bool', true);

    expect($int->value())->toBe(12);
    expect($int->path())->toBe('int');
    expect($int->toArray())->toBe([
        'value' => 12,
        'options' => [ 'int' => 1, 'float' => 12.2, 'str' => 'nope', 'bool' => true ]
    ]);

    $float = new LensFloatNode('float', $bands, $root, 15.13);
    
    expect($float->value())->toBe(15.13);
    expect($float->path())->toBe('float');
    expect($float->toArray())->toBe([
        'value' => 15.13,
        'options' => []
    ]);
    
    $string = new LensStringNode('string', $bands, $root, 'Hello there');

    expect($string->value())->toBe('Hello there');
    expect($string->path())->toBe('string');
    expect($string->toArray())->toBe([
        'value' => 'Hello there',
        'options' => []
    ]);
});

test('computed node works correctly', function () {
    $bandsRoot = new BandsBranch(0, [ 'type' , 'brand' ]);
    $bandsRoot->add(1, 3.0, [ 'type' => 'SUV', 'brand' => 'BMW' ]);
    $bandsRoot->add(2, 2.0, [ 'type' => 'SUV', 'brand' => 'Audi' ]);
    $bandsRoot->add(3, 4.0, [ 'type' => 'Kombi', 'brand' => 'Audi' ]);
    $bandsRoot->add(4, 1.0, [ 'type' => 'Sedan', 'brand' => 'BMW' ]);

    $bands = new Bands();
    $bands->add('main', new TotalFoodsSplitter(), new SplitterBands($bandsRoot));
    $root = new LensRootNode($bands);
    $root->root()->int('const', 3);
    $root->root()->computed('first', function(Bands $bands, LensRootNode $root) {
        /** @var LensIntegerNode $const */
        $const = $root->get('const');
        
        $suvs = $bands->for('main')->where('type', 'SUV')->value();

        $constVal = (int) $const->value();
        return "$suvs ($constVal)";
    });

    /** @var LensComputedNode $first */
    $first = $root->get('first');
    expect($first->value())->toBe('5 (3)');
    expect($first->bandsIndexes())->toBe([1 => [1, 2]]);
});

test('computed node throws error on loop', function () {
    $bandsRoot = new BandsBranch(0, [ 'type' , 'brand' ]);

    $bands = new Bands();
    $bands->add('main', new TotalFoodsSplitter(), new SplitterBands($bandsRoot));

    $root = new LensRootNode($bands);
    $root->root()->int('const', 3);
    $root->root()->computed('first', function(Bands $bands, LensRootNode $root) {
        return $root->value('second');
    });
    $root->root()->computed('second', function(Bands $bands, LensRootNode $root) {
        return $root->value('first');
    });

    $root->value('first');

})->throws(RefractException::class, "Node 'first' is locked, by another node");

test('computed node throws error on wrong return value', function () {
    $bandsRoot = new BandsBranch(0, [ 'type' , 'brand' ]);

    $bands = new Bands();
    $bands->add('main', new TotalFoodsSplitter(), new SplitterBands($bandsRoot));

    $root = new LensRootNode($bands);
    $root->root()->int('const', 3);
    $root->root()->computed('first', function(Bands $bands, LensRootNode $root) {
        return new Bands();
    });

    $root->value('first');

})->throws(RefractException::class, "Computed node 'first' handle return value must be type int|float|string|bool");

test('object node works properly', function () {
    $bandsRoot = new BandsBranch(0, [ 'type' , 'brand' ]);

    $bands = new Bands();
    $bands->add('main', new TotalFoodsSplitter(), new SplitterBands($bandsRoot));
    $root = new LensRootNode($bands);

    $node = new LensObjectNode('', $bands, $root);

    $node->int('int', 11);
    $node->float('float', 12.5);
    $node->string('string', 'Hello there');
    $node->computed('computed', function(Bands $bands, LensRootNodeInterface $root) {
        return 'General Calculator!';
    });
    $node->object('son')->int('grade', 4);
    $node->array('array')->int(134);

    expect($node->toArray())->toBe([
        'int' => [ 'value' => 11, 'options' => [] ],
        'float' => [ 'value' => 12.5, 'options' => [] ],
        'string' => [ 'value' => 'Hello there', 'options' => [] ],
        'computed' => [ 'value' => 'General Calculator!', 'options' => [] ],
        'son' => [
            'grade' => [ 'value' => 4, 'options' => [] ]
        ],
        'array' => [
            [ 'value' => 134, 'options' => [] ]
        ]
    ]);
});


test('array node works properly', function () {
    $bandsRoot = new BandsBranch(0, [ 'type' , 'brand' ]);

    $bands = new Bands();
    $bands->add('main', new TotalFoodsSplitter(), new SplitterBands($bandsRoot));
    $root = new LensRootNode($bands);

    $node = new LensArrayNode('', $bands, $root);

    $node->int(11);
    $node->float(12.5);
    $node->string('Hello there');
    $node->computed(function(Bands $bands, LensRootNodeInterface $root) {
        return 'General Calculator!';
    });
    $node->object()->int('grade', 4);
    $node->array()->int(134);

    expect($node->toArray())->toBe([
        [ 'value' => 11, 'options' => [] ],
        [ 'value' => 12.5, 'options' => [] ],
        [ 'value' => 'Hello there', 'options' => [] ],
        [ 'value' => 'General Calculator!', 'options' => [] ],
        [ 'grade' => [ 'value' => 4, 'options' => [] ] ],
        [ [ 'value' => 134, 'options' => [] ] ]
    ]);
});

test('collection node exception on adding child with incorrect path name', function () {
    $bandsRoot = new BandsBranch(0, [ 'type' , 'brand' ]);

    $bands = new Bands();
    $bands->add('main', new TotalFoodsSplitter(), new SplitterBands($bandsRoot));
    $root = new LensRootNode($bands);

    $node = new LensObjectNode('', $bands, $root);

    $node->int('user.0.key', 12);

})->throws(RefractException::class, 'Node keys cannot contain dot (.) character');

test('collection node get not found', function () {
    $bandsRoot = new BandsBranch(0, [ 'type' , 'brand' ]);

    $bands = new Bands();
    $bands->add('main', new TotalFoodsSplitter(), new SplitterBands($bandsRoot));
    $root = new LensRootNode($bands);

    $node = new LensObjectNode('', $bands, $root);

    expect($node->get('user'))->toBeNull();
});

test('meta data returns correct array', function () {
    $meta = new LensMetaData();

    $meta->set('int', 12)
        ->set('float', 13.4)
        ->set('text', 'Oh HI!');

    expect($meta->toArray())->toBe([
        'int' => 12,
        'float' => 13.4,
        'text' => 'Oh HI!',
    ]);
});

test('root node get value for collection node', function () {

    $bandsRoot = new BandsBranch(0, [ 'type' , 'brand' ]);

    $bands = new Bands();
    $bands->add('main', new TotalFoodsSplitter(), new SplitterBands($bandsRoot));
    $root = new LensRootNode($bands);

    $root->root()->object('item')->object('ball')->int('age', 78);

    expect($root->value('item.ball'))->toBe('');
});

test('root node gets correct meta', function () {
    
    $bandsRoot = new BandsBranch(0, [ 'type' , 'brand' ]);

    $bands = new Bands();
    $bands->add('main', new TotalFoodsSplitter(), new SplitterBands($bandsRoot));
    $root = new LensRootNode($bands);
    $root->meta()->set('int', 12)
        ->set('float', 13.4)
        ->set('text', 'Oh HI!');

    expect($root->meta()->toArray())->toBe([
        'int' => 12,
        'float' => 13.4,
        'text' => 'Oh HI!',
    ]);
});

test('root node have method checks paths correctly', function () {
    
    $bandsRoot = new BandsBranch(0, [ 'type' , 'brand' ]);

    $bands = new Bands();
    $bands->add('main', new TotalFoodsSplitter(), new SplitterBands($bandsRoot));
    $root = new LensRootNode($bands);
    $root = new LensRootNode($bands);
    $obj = $root->root()->object('obj');
    $obj->int('val', 12);
    $obj->string('name', 'test');
    $root->root()->float('map', 12.4);

    expect($root->have('obj'))->toBeTrue();
    expect($root->have('obj.val'))->toBeTrue();
    expect($root->have('obj.name'))->toBeTrue();
    expect($root->have('map'))->toBeTrue();
    expect($root->have('obj.nody'))->toBeFalse();
});

test('root node eachValueNode() method runs over correct nodes', function () {
    
    $bandsRoot = new BandsBranch(0, [ 'type' ]);

    $bands = new Bands();
    $bands->add('main', new TotalFoodsSplitter(), new SplitterBands($bandsRoot));
    $root = new LensRootNode($bands);
    $obj = $root->root()->object('obj');
    $obj->int('val', 12);
    $obj->string('name', 'test');
    $root->root()->float('map', 12.4);
   
    $paths = [];
    $root->eachValueNode(function (LensValueNode $node) use (&$paths) {
        $paths[] = $node->path();
    });

    expect($paths)->toBe([
        'obj.val',
        'obj.name',
        'map',
    ]);
});

test('root node get throws on missing key', function () {
    
    $bandsRoot = new BandsBranch(0, [ 'type' , 'brand' ]);

    $bands = new Bands();
    $bands->add('main', new TotalFoodsSplitter(), new SplitterBands($bandsRoot));
    $root = new LensRootNode($bands);

    $root->get('missing.item.goes.here');

})->throws(RefractException::class, "No node was found for path: 'missing.item.goes.here'");