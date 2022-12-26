<?php

beforeEach(function () {
    $host = $this->createHost('localhost', 65000, ['diskspace']);

    $this->check = $host->checks->first();

    $this->check->custom_properties = [
        'customName' => 'customValue',
        'nested' => [
        'customName' => 'nested customValue',
        ],
    ];

    $this->check->save();
});

it('can determine if a media item has a custom property', function () {
    expect($this->check->hasCustomProperty('customName'))->toBeTrue();
    expect($this->check->hasCustomProperty('nested.customName'))->toBeTrue();

    expect($this->check->hasCustomProperty('nonExisting'))->toBeFalse();
    expect($this->check->hasCustomProperty('nested.nonExisting'))->toBeFalse();
});

it('can get a custom property', function () {
    expect($this->check->getCustomProperty('customName'))->toEqual('customValue');
    expect($this->check->getCustomProperty('nested.customName'))->toEqual('nested customValue');

    expect($this->check->getCustomProperty('nonExisting'))->toBeNull();
    expect($this->check->getCustomProperty('nested.nonExisting'))->toBeNull();
});

it('can set a custom property', function () {
    $this->check->setCustomProperty('anotherName', 'anotherValue');

    expect($this->check->getCustomProperty('anotherName'))->toEqual('anotherValue');
    expect($this->check->getCustomProperty('customName'))->toEqual('customValue');

    $this->check->setCustomProperty('nested.anotherName', 'anotherValue');
    expect($this->check->getCustomProperty('nested.anotherName'))->toEqual('anotherValue');
});

it('can forget a custom property', function () {
    expect($this->check->hasCustomProperty('customName'))->toBeTrue();
    expect($this->check->hasCustomProperty('nested.customName'))->toBeTrue();

    $this->check->forgetCustomProperty('customName');
    $this->check->forgetCustomProperty('nested.customName');

    expect($this->check->hasCustomProperty('customName'))->toBeFalse();
    expect($this->check->hasCustomProperty('nested.customName'))->toBeFalse();
});

it('returns a fallback if a custom property isnt set', function () {
    expect($this->check->getCustomProperty('imNotHere', 'foo'))->toEqual('foo');
});
