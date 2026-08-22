<?php

use Illuminate\Support\Carbon;
use Tanzar\Refract\Enums\ParamTypes;
use Tanzar\Refract\Exceptions\RefractException;
use Tanzar\Refract\Splitter\RequiredParams;
use Tanzar\Refract\Splitter\SplitterParams;

beforeEach(function() use (&$requiredParams) {
    $requiredParams = new RequiredParams();
    
    $requiredParams->date('created', Carbon::parse('2026-08-22'));
    $requiredParams->int('user', 123);
    $requiredParams->float('price', 1.53);
    $requiredParams->string('transaction_type', 'cash');
    $requiredParams->bool('isDead', true);

});

test('SplitterParams date', function() use (&$requiredParams) {
    /** @var RequiredParams $requiredParams */
    $instance = new SplitterParams($requiredParams, 1.0);

    expect($instance->getValue('created'))->toBe('2026-08-22');

    $instance->date('created', Carbon::parse('2026-08-21'));

    expect($instance->getType('created'))->toBe(ParamTypes::DATE);

    expect($instance->getValue('created'))->toBe('2026-08-21');
});

test('SplitterParams int', function() use (&$requiredParams) {
    /** @var RequiredParams $requiredParams */
    $instance = new SplitterParams($requiredParams, 1.0);

    expect($instance->getValue('user'))->toBe(123);

    $instance->int('user', 321);

    expect($instance->getType('user'))->toBe(ParamTypes::INTEGER);

    expect($instance->getValue('user'))->toBe(321);
});

test('SplitterParams float', function() use (&$requiredParams) {
    /** @var RequiredParams $requiredParams */
    $instance = new SplitterParams($requiredParams, 1.0);

    expect($instance->getValue('price'))->toBe(1.53);

    $instance->float('price', 2.64);

    expect($instance->getType('price'))->toBe(ParamTypes::FLOAT);

    expect($instance->getValue('price'))->toBe(2.64);
});

test('SplitterParams string', function() use (&$requiredParams) {
    /** @var RequiredParams $requiredParams */
    $instance = new SplitterParams($requiredParams, 1.0);

    expect($instance->getValue('transaction_type'))->toBe('cash');

    $instance->string('transaction_type', 'credit');

    expect($instance->getType('transaction_type'))->toBe(ParamTypes::STRING);

    expect($instance->getValue('transaction_type'))->toBe('credit');
});

test('SplitterParams bool', function() use (&$requiredParams) {
    /** @var RequiredParams $requiredParams */
    $instance = new SplitterParams($requiredParams, 1.0);

    expect($instance->getValue('isDead'))->toBe(true);

    $instance->bool('isDead', false);

    expect($instance->getType('isDead'))->toBe(ParamTypes::BOOLEAN);

    expect($instance->getValue('isDead'))->toBe(false);
});

test('SplitterParams getModelValue', function() use (&$requiredParams) {
    /** @var RequiredParams $requiredParams */
    $instance = new SplitterParams($requiredParams, 15.0);

    expect($instance->getModelValue())->toBe(15.0);
});

test('SplitterParams getKeys', function() use (&$requiredParams) {
    /** @var RequiredParams $requiredParams */
    $instance = new SplitterParams($requiredParams, 15.0);

    expect($instance->getKeys())->toBe([ 'created', 'user', 'price', 'transaction_type', 'isDead' ]);
});

test('SplitterParams invalid key', function() use (&$requiredParams) {
    /** @var RequiredParams $requiredParams */
    $instance = new SplitterParams($requiredParams, 15.0);

    $instance->date('created', Carbon::parse('2026-08-22'));

    expect(fn() => $instance->date('invalid_key', Carbon::parse('2026-08-22')))
        ->toThrow(RefractException::class, 'Key invalid_key not allowed, use correct type or add it to required');
});