<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Spatie\ServerMonitor\Events\CheckFailed;

beforeEach(function () {
    $this->skipIfDummySshServerIsNotRunning();

    Event::fake();

    $this->check = $this->createHost()->checks->first();
});

it('the faield event will be fired when a check failed', function () {
    $this->letSshServerRespondWithDiskspaceUsagePercentage(99);

    Event::assertNotDispatched(CheckFailed::class);

    Artisan::call('server-monitor:run-checks');

    Event::assertDispatched(CheckFailed::class, function (CheckFailed $event) {
        return $event->check->id === $this->check->id;
    });
});
