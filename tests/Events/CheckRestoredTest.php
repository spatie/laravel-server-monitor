<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Spatie\ServerMonitor\Events\CheckRestored;

beforeEach(function () {
    $this->skipIfDummySshServerIsNotRunning();

    Event::fake();

    $this->check = $this->createHost()->checks->first();
});

it('the recovered event will be fired when an check succeeds after it has failed', function () {
    $this->letSshServerRespondWithDiskspaceUsagePercentage(99);

    Artisan::call('server-monitor:run-checks');

    $this->letSshServerRespondWithDiskspaceUsagePercentage(20);

    $this->progressMinutes(60 * 24);

    Event::assertNotDispatched(CheckRestored::class);

    Artisan::call('server-monitor:run-checks');

    Event::assertDispatched(CheckRestored::class, function (CheckRestored $event) {
        return $event->check->id === $this->check->id;
    });
});
