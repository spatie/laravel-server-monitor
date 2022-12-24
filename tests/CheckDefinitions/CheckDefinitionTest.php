<?php

use Spatie\ServerMonitor\CheckDefinitions\CheckDefinition;
use Spatie\ServerMonitor\CheckDefinitions\Diskspace;
use Spatie\ServerMonitor\Models\Check;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;
use Symfony\Component\Process\Process;

beforeEach(function () {
    $this->createHost('localhost', 65000, ['diskspace']);

    $this->check = Check::first();

    $this->diskspaceCheckDefinition = (new Diskspace())->setCheck($this->check);
});

it('will mark the check as failed when passing a failed process', function () {
    $process = $this->getFailedProcess();

    $this->diskspaceCheckDefinition->determineResult($process);

    $this->check->fresh();

    expect($this->check->status)->toEqual(CheckStatus::FAILED);
});

it('will mark the check as failed when a check definition throws an exception', function () {
    $checkDefinition = new class extends CheckDefinition {
        public function resolve(Process $process)
        {
            throw new Exception('my exception message');
        }

        public function performNextRunInMinutes(): int
        {
            return 0;
        }
    };

    $checkDefinition->setCheck($this->check);

    $process = $this->getSuccessfulProcessWithOutput();

    $checkDefinition->determineResult($process);

    $this->check->fresh();

    expect($this->check->status)->toEqual(CheckStatus::FAILED);

    expect($this->check->last_run_message)->toEqual('Exception occurred: my exception message');
});
