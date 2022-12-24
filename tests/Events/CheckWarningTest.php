<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Spatie\ServerMonitor\Events\CheckWarning;

beforeEach(function () {
    $this->skipIfDummySshServerIsNotRunning();

    Event::fake();

    $this->check = $this->createHost()->checks->first();
});

it('the succeeded event will be fired when a check succeeds', function () {
    $this->letSshServerRespondWithDiskspaceUsagePercentage(85);

    Event::assertNotDispatched(CheckWarning::class);

    Artisan::call('server-monitor:run-checks');

    Event::assertDispatched(CheckWarning::class, function (CheckWarning $event) {
        return $event->check->id === $this->check->id;
    });
});
