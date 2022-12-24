<?php

use Carbon\Carbon;
use Spatie\ServerMonitor\CheckDefinitions\Diskspace;
use Spatie\ServerMonitor\Exceptions\InvalidCheckDefinition;
use Spatie\ServerMonitor\Models\Check;

beforeEach(function () {
    $this->createHost('my-host', null, ['diskspace']);

    $this->check = Check::first();
});

it('can get its definition', function () {
    expect($this->check->getDefinition())->toBeInstanceOf(Diskspace::class);
});

it('will throw an exception when it has an unknown type', function () {
    $this->check->type = 'bla bla';
    $this->check->save();

    $this->expectException(InvalidCheckDefinition::class);

    $this->check->getDefinition();
});

it('will determine that it should be run', function () {
    expect($this->check->shouldRun())->toBeTrue();
});

it('will determine that it should not run when it is disabled', function () {
    $this->check->enabled = false;

    $this->check->save();

    expect($this->check->shouldRun())->toBeFalse();
});

it('will determine that it should not be run until after a certain period of time', function () {
    $nextRunInMinutes = 5;

    $this->check->last_ran_at = Carbon::now();

    $this->check->next_run_in_minutes = $nextRunInMinutes;

    $this->check->save();

    foreach (range(1, $nextRunInMinutes) as $pastMinutes) {
        expect($this->check->shouldRun())->toBeFalse();

        $this->progressMinutes(1);
    }

    expect($this->check->shouldRun())->toBeTrue();
});
