<?php

use Spatie\ServerMonitor\CheckDefinitions\Memcached;
use Spatie\ServerMonitor\Models\Check;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;

beforeEach(function () {
    $this->createHost('localhost', 65000, ['elasticsearch']);

    $this->check = Check::first();

    $this->memcachedDefinition = (new Memcached())->setCheck($this->check);
});

it('can determine success', function () {
    $process = $this->getSuccessfulProcessWithOutput(
        '* memcached is running'
    );

    $this->memcachedDefinition->resolve($process);

    $this->check->fresh();

    expect($this->check->last_run_message)->toContain('is running');
    expect($this->check->status)->toEqual(CheckStatus::SUCCESS);
});

it('can determine failure', function () {
    $process = $this->getSuccessfulProcessWithOutput(
        ''
    );

    $this->memcachedDefinition->resolve($process);

    $this->check->fresh();

    expect($this->check->last_run_message)->toContain('is not running');
    expect($this->check->status)->toEqual(CheckStatus::FAILED);
});
