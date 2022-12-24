<?php

use Spatie\ServerMonitor\CheckDefinitions\Diskspace;
use Spatie\ServerMonitor\Models\Check;

beforeEach(function () {
    $this->createHost('localhost', 65000, ['diskspace']);

    $this->check = Check::first();

    $this->diskspaceCheckDefinition = (new Diskspace())->setCheck($this->check);
});

it('can handle its command output', function (int $diskspaceUsed, string $status) {
    $process = $this->getSuccessfulProcessWithOutput(
        'Filesystem 512-blocks      Used Available Capacity  Mounted on\n'.
        "/dev/disk1  974700800 830137776 144051024   {$diskspaceUsed}%    /"
    );

    $this->diskspaceCheckDefinition->resolve($process);

    $this->check->fresh();

    expect($this->check->last_run_message)->toContain("{$diskspaceUsed}%");
    expect($this->check->status)->toEqual($status);
})->with('percentage');
