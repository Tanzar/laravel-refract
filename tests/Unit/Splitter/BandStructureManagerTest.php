<?php

use Tanzar\Refract\Services\SplitterUpdate\BandStructureManager;
use Tanzar\Refract\Splitter\RequiredParams;
use Tanzar\Refract\Splitter\SplitterParams;
use Workbench\App\Splitters\TotalFoodsSplitter;

test('on empty database', function () {

    $required = new RequiredParams()->int('user')->int('group');

    $params = [
        new SplitterParams($required, 1, 1)->int('user', 1)->int('group', 1),
        new SplitterParams($required, 1, 2)->int('user', 1)->int('group', 1),
        new SplitterParams($required, 1, 3)->int('user', 2)->int('group', 2),
        new SplitterParams($required, 1, 4)->int('user', 2)->int('group', 2),
        new SplitterParams($required, 1, 5)->int('user', 3)->int('group', 2),
        new SplitterParams($required, 1, 6)->int('user', 4)->int('group', 2),
    ];

    $manager = new BandStructureManager(new TotalFoodsSplitter());

    foreach ($params as $param) {
        $manager->analyze($param);
    }

    $manager->verify();

    $this->assertDatabaseCount('refract_splitters', 1);
    $this->assertDatabaseHas('refract_splitters', [
        'splitter_type' => 'Workbench\\App\\Splitters\\TotalFoodsSplitter',
        'model_type' => 'Workbench\\App\\Models\\Food',
        'bands_count' => 4,
        'encoded_params' => 'category:string:general;price:float:0;'
    ]);

    $this->assertDatabaseCount('refract_params', 4);

    $this->assertDatabaseHas('refract_params', [ 'type' => 'int', 'raw_value' => '1', 'int_value' => 1 ]);
    $this->assertDatabaseHas('refract_params', [ 'type' => 'int', 'raw_value' => '2', 'int_value' => 2 ]);
    $this->assertDatabaseHas('refract_params', [ 'type' => 'int', 'raw_value' => '3', 'int_value' => 3 ]);
    $this->assertDatabaseHas('refract_params', [ 'type' => 'int', 'raw_value' => '4', 'int_value' => 4 ]);

    $this->assertDatabaseCount('refract_bands', 4);
    $this->assertDatabaseHas('refract_bands',
        [ 'splitter_id' => 1, 'band_index' => 1, 'signature_hash' => '89feb34d6641125a22b9fa04a03e5bf3', 'current_value' => 0 ]
    );
    $this->assertDatabaseHas('refract_bands',
        [ 'splitter_id' => 1, 'band_index' => 2, 'signature_hash' => 'e43fb9911e20eefbabf2daed784a2209', 'current_value' => 0 ]
    );
    $this->assertDatabaseHas('refract_bands',
        [ 'splitter_id' => 1, 'band_index' => 3, 'signature_hash' => '4bc0bad2c3d7b7d6ff885d9c4227715d', 'current_value' => 0 ]
    );
    $this->assertDatabaseHas('refract_bands',
        [ 'splitter_id' => 1, 'band_index' => 4, 'signature_hash' => '6c8601652bb7bb2b378baf063cdd2a9c', 'current_value' => 0 ]
    );

    $this->assertDatabaseCount('refract_bands_params', 8);
    $this->assertDatabaseHas('refract_bands_params',
        [ 'splitter_id' => 1, 'band_index' => 1, 'param_id' => 1, 'key_name' => 'user' ]
    );
    $this->assertDatabaseHas('refract_bands_params',
        [ 'splitter_id' => 1, 'band_index' => 1, 'param_id' => 1, 'key_name' => 'group' ]
    );
    $this->assertDatabaseHas('refract_bands_params',
        [ 'splitter_id' => 1, 'band_index' => 2, 'param_id' => 2, 'key_name' => 'user' ]
    );
    $this->assertDatabaseHas('refract_bands_params',
        [ 'splitter_id' => 1, 'band_index' => 2, 'param_id' => 2, 'key_name' => 'group' ]
    );
    $this->assertDatabaseHas('refract_bands_params',
        [ 'splitter_id' => 1, 'band_index' => 3, 'param_id' => 3, 'key_name' => 'user' ]
    );
    $this->assertDatabaseHas('refract_bands_params',
        [ 'splitter_id' => 1, 'band_index' => 3, 'param_id' => 2, 'key_name' => 'group' ]
    );
    $this->assertDatabaseHas('refract_bands_params',
        [ 'splitter_id' => 1, 'band_index' => 4, 'param_id' => 4, 'key_name' => 'user' ]
    );
    $this->assertDatabaseHas('refract_bands_params',
        [ 'splitter_id' => 1, 'band_index' => 4, 'param_id' => 2, 'key_name' => 'group' ]
    );

});