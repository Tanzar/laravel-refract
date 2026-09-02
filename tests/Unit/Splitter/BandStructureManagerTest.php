<?php

use Tanzar\Refract\Services\BandStructureManager;
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
        [ 
            'splitter_id' => 1,
            'band_index' => 1,
            'signature_hash' => '18ae13c289dd3b61d0f1ec9754c0ebad255b199868177ed60479e97e8d0e46f8',
            'current_value' => 0
        ]
    );
    $this->assertDatabaseHas('refract_bands',
        [
            'splitter_id' => 1,
            'band_index' => 2,
            'signature_hash' => '181a162cff83e9f168a710cc520285d6a598cc7c5e57a65f047999de6e2179ed',
            'current_value' => 0
        ]
    );
    $this->assertDatabaseHas('refract_bands',
        [
            'splitter_id' => 1,
            'band_index' => 3,
            'signature_hash' => '473a19ec2e4398ed0bc680cd2776159cf5a9c5c2f8dbe5734899a4a9b954d400', 
            'current_value' => 0
        ]
    );
    $this->assertDatabaseHas('refract_bands',
        [
            'splitter_id' => 1,
            'band_index' => 4,
            'signature_hash' => 'a1aa868a2fd8381f3f62d6965cd7fd96e3784eb1b54776f15eaba235f1b59b67',
            'current_value' => 0
        ]
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

test('nothing to insert', function () {

    $manager = new BandStructureManager(new TotalFoodsSplitter());

    $manager->verify();

    $this->assertDatabaseCount('refract_splitters', 1);
    $this->assertDatabaseHas('refract_splitters', [
        'splitter_type' => 'Workbench\\App\\Splitters\\TotalFoodsSplitter',
        'model_type' => 'Workbench\\App\\Models\\Food',
        'bands_count' => 0,
        'encoded_params' => 'category:string:general;price:float:0;'
    ]);

    $this->assertDatabaseCount('refract_params', 0);
    $this->assertDatabaseCount('refract_bands', 0);
    $this->assertDatabaseCount('refract_bands_params', 0);

});