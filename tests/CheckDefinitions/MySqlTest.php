<?php

use Spatie\ServerMonitor\CheckDefinitions\MySql;
use Spatie\ServerMonitor\Models\Check;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;

beforeEach(function () {
    $this->createHost('localhost', 65000, ['elasticsearch']);

    $this->check = Check::first();

    $this->mySqlCheckDefintion = (new MySql())->setCheck($this->check);
});

it('can determine success', function () {
    $process = $this->getSuccessfulProcessWithOutput(
        '1410 ?    00:20:36 mysqld'
    );

    $this->mySqlCheckDefintion->resolve($process);

    $this->check->fresh();

    expect($this->check->last_run_message)->toContain('is running');
    expect($this->check->status)->toEqual(CheckStatus::SUCCESS);
});

it('can determine failure', function () {
    $process = $this->getSuccessfulProcessWithOutput(
        ''
    );

    $this->mySqlCheckDefintion->resolve($process);

    $this->check->fresh();

    expect($this->check->last_run_message)->toContain('is not running');
    expect($this->check->status)->toEqual(CheckStatus::FAILED);
});
