<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Spatie\ServerMonitor\Events\CheckSucceeded;

beforeEach(function () {
    $this->skipIfDummySshServerIsNotRunning();

    Event::fake();

    $this->check = $this->createHost()->checks->first();
});

it('the succeeded event will be fired when a check succeeds', function () {
    $this->letSshServerRespondWithDiskspaceUsagePercentage(20);

    Event::assertNotDispatched(CheckSucceeded::class);

    Artisan::call('server-monitor:run-checks');

    Event::assertDispatched(CheckSucceeded::class, function (CheckSucceeded $event) {
        return $event->check->id === $this->check->id;
    });
});
