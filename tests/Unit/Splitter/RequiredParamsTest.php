<?php

use Illuminate\Support\Carbon;
use Tanzar\Refract\Enums\ParamTypes;
use Tanzar\Refract\Splitter\RequiredParams;

test('RequiredParams date', function() {
    
    $instance = new RequiredParams();

    expect($instance->have('created', ParamTypes::DATE))->toBe(false);

    $instance->date('created', Carbon::parse('2026-08-22'));

    expect($instance->getType('created'))->toBe(ParamTypes::DATE);

    expect($instance->getDefault('created'))->toBe('2026-08-22');
    
    expect($instance->have('created', ParamTypes::DATE))->toBe(true);

});

test('RequiredParams int', function() {
    
    $instance = new RequiredParams();

    expect($instance->have('user', ParamTypes::INTEGER))->toBe(false);

    $instance->int('user', 123);

    expect($instance->getType('user'))->toBe(ParamTypes::INTEGER);

    expect($instance->getDefault('user'))->toBe(123);
    
    expect($instance->have('user', ParamTypes::INTEGER))->toBe(true);
});

test('RequiredParams float', function() {
    
    $instance = new RequiredParams();

    expect($instance->have('price', ParamTypes::FLOAT))->toBe(false);

    $instance->float('price', 1.53);

    expect($instance->getType('price'))->toBe(ParamTypes::FLOAT);

    expect($instance->getDefault('price'))->toBe(1.53);
    
    expect($instance->have('price', ParamTypes::FLOAT))->toBe(true);
});

test('RequiredParams string', function() {
    
    $instance = new RequiredParams();

    expect($instance->have('transaction_type', ParamTypes::STRING))->toBe(false);

    $instance->string('transaction_type', 'cash');

    expect($instance->getType('transaction_type'))->toBe(ParamTypes::STRING);

    expect($instance->getDefault('transaction_type'))->toBe('cash');
    
    expect($instance->have('transaction_type', ParamTypes::STRING))->toBe(true);
});

test('RequiredParams boolean', function() {
    
    $instance = new RequiredParams();

    expect($instance->have('isDead', ParamTypes::BOOLEAN))->toBe(false);

    $instance->bool('isDead', true);

    expect($instance->getType('isDead'))->toBe(ParamTypes::BOOLEAN);

    expect($instance->getDefault('isDead'))->toBe(true);
    
    expect($instance->have('isDead', ParamTypes::BOOLEAN))->toBe(true);
});

test('RequiredParams getKeys() method', function() {
    
    $instance = new RequiredParams();

    $instance->date('created', Carbon::parse('2026-08-22'));
    $instance->int('user', 123);
    $instance->float('price', 1.53);
    $instance->string('transaction_type', 'cash');
    $instance->bool('isDead', true);

    expect($instance->getKeys())
        ->toBe([ 'created', 'user', 'price', 'transaction_type', 'isDead' ]);

    expect($instance->encode())
        ->toBe("created:date:2026-08-22;user:int:123;price:float:1.53;transaction_type:string:cash;isDead:bool:1;");
});

