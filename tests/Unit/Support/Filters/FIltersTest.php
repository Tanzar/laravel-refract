<?php

use Illuminate\Support\Carbon;
use Tanzar\Refract\Support\Filters\Filters;
use Tanzar\Refract\Support\Filters\FiltersRef;

test('seters and getters', function () {

    $filters = new Filters();

    $filters->float('price', 15.54)
        ->date('today', Carbon::now())
        ->string('message', 'Hello there')
        ->int('id', 13)
        ->bool('isDead', true);

    expect($filters->get('price'))->toBe(15.54);
    expect($filters->get('today')->format('Y-m-d H:i:s'))->toBe(Carbon::now()->format('Y-m-d H:i:s'));
    expect($filters->get('message'))->toBe('Hello there');
    expect($filters->get('id'))->toBe(13);
    expect($filters->get('isDead'))->toBeTrue();
    expect($filters->get('nothing'))->toBeNull();
});

test('keys are ordered', function() {

    $filters = new Filters();

    $filters->float('price', 15.54)
        ->date('today', Carbon::now())
        ->string('message', 'Hello there')
        ->int('id', 13)
        ->bool('isDead', true);

    $keys = $filters->keys();

    expect($keys[0])->toBe('id');
    expect($keys[1])->toBe('isDead');
    expect($keys[2])->toBe('message');
    expect($keys[3])->toBe('price');
    expect($keys[4])->toBe('today');
});

test('hashing', function() {

    $filters = new Filters();

    $filters->float('price', 15.54)
        ->date('today', Carbon::now())
        ->string('message', 'Hello there')
        ->int('id', 13)
        ->bool('isDead', true);

    $different = new Filters();

    $different->float('price', 16.54)
        ->date('today', Carbon::now())
        ->string('message', 'Hello there')
        ->int('id', 13)
        ->bool('isDead', true);

    expect($filters->hash() !== $different->hash())->toBeTrue();
    
    $same = new Filters();

    $same->float('price', 15.54)
        ->date('today', Carbon::now())
        ->string('message', 'Hello there')
        ->int('id', 13)
        ->bool('isDead', true);

    expect($filters->hash() === $same->hash())->toBeTrue();
    
});

test('toArray generates oredred array', function () {
    
    $filters = new Filters();

    $filters->float('price', 15.54)
        ->date('today', Carbon::parse('2026-10-10 10:23:15'))
        ->string('message', 'Hello there')
        ->int('id', 13)
        ->bool('isDead', true);

    $array = $filters->toArray();

    expect($array)->toBe([
        'id' => [ 'value' => 13, 'type' => 'int' ],
        'isDead' => [ 'value' => true, 'type' => 'bool' ],
        'message' => [ 'value' => 'Hello there', 'type' => 'string' ],
        'price' => [ 'value' => 15.54, 'type' => 'float' ],
        'today' => [ 'value' => '2026-10-10', 'type' => 'date' ],
    ]);
});

test('filters refference', function() {
    $ref = Filters::ref('node');

    expect($ref)->toBeInstanceOf(FiltersRef::class);
    expect($ref->key())->toBe('node');
});