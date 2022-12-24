<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Spatie\ServerMonitor\Models\Check;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;
use Spatie\ServerMonitor\Notifications\Notifications\CheckFailed;

function getNotifiable()
{
    $notifiableClass = config('server-monitor.notifications.notifiable');

    return app($notifiableClass);
}

beforeEach(function () {
    $this->skipIfDummySshServerIsNotRunning();

    $this->host = $this->createHost('localhost', 65000, ['diskspace']);
});

it('can run a successful check', function () {
    $this->letSshServerRespondWithDiskspaceUsagePercentage(40);

    Artisan::call('server-monitor:run-checks');

    $check = Check::where('host_id', $this->host->id)->where('type', 'diskspace')->first();

    expect($check->last_run_message)->toEqual('usage at 40%');
    expect($check->status)->toEqual(CheckStatus::SUCCESS);
});

it('can run a check that issues a warning', function () {
    $this->letSshServerRespondWithDiskspaceUsagePercentage(85);

    Artisan::call('server-monitor:run-checks');

    $check = Check::where('host_id', $this->host->id)->where('type', 'diskspace')->first();

    expect($check->last_run_message)->toEqual('usage at 85%');
    expect($check->status)->toEqual(CheckStatus::WARNING);
});

it('can run a failing check', function () {
    $this->letSshServerRespondWithDiskspaceUsagePercentage(95);

    Artisan::call('server-monitor:run-checks');

    $check = Check::where('host_id', $this->host->id)->where('type', 'diskspace')->first();

    expect($check->last_run_message)->toEqual('usage at 95%');
    expect($check->status)->toEqual(CheckStatus::FAILED);
});

it('will throttle failed notifications', function () {
    $this->app['config']->set(
        'server-monitor.notifications.notifications.'.CheckFailed::class,
        ['slack']
    );

    Notification::fake();

    $this->letSshServerRespondWithDiskspaceUsagePercentage(95);

    Artisan::call('server-monitor:run-checks');

    Notification::assertSentTo(getNotifiable(), CheckFailed::class);

    $minutes = config('server-monitor.notifications.throttle_failing_notifications_for_minutes');

    $this->progressMinutes($minutes - 1);

    $this->resetNotificationAssertions();

    Artisan::call('server-monitor:run-checks');

    Notification::assertNotSentTo(getNotifiable(), CheckFailed::class);

    $this->progressMinutes(1);

    Artisan::call('server-monitor:run-checks');

    Notification::assertSentTo(getNotifiable(), CheckFailed::class);
});
