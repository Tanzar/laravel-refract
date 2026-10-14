<?php

use Tanzar\Refract\Support\Bands\SplitterBands;
use Tanzar\Refract\Support\Bands\Tree\BandsBranch;

test('correctly manages finders', function() {
    $branch = new BandsBranch(0, [ 'group', 'type' ]);

    $branch->add(1, 10.0, [ 'type' => 'paid', 'group' => 1 ]);
    $branch->add(2, 12.0, [ 'type' => 'paid', 'group' => 2 ]);
    $branch->add(3, 5.0, [ 'type' => 'waiting', 'group' => 1 ]);
    $branch->add(4, 23.0, [ 'type' => 'waiting', 'group' => 2 ]);
    $branch->add(5, 11.0, [ 'type' => 'canceled', 'group' => 1 ]);

    $bands = new SplitterBands($branch);

    expect($bands->find()->where('type', 'paid')->value())->toBe(22.0);
    expect($bands->find()->where('type', 'waiting')->value())->toBe(28.0);
    expect($bands->pullRequestedBands())->toBe([1, 2, 3, 4]);
    expect($bands->pullRequestedBands())->toBe([]);

    expect($bands->find()->where('group', 1)->value())->toBe(26.0);
});