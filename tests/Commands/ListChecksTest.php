<?php

namespace Spatie\ServerMonitor\Test\Commands;

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Spatie\ServerMonitor\Models\Check;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;
use Spatie\ServerMonitor\Models\Host;

beforeEach(function () {
    $this->addHosts = function ($hostNames, array $healthyChecks = [], array $unhealthyChecks = []): void {
        if (! is_array($hostNames)) {
            $hostNames = [$hostNames];
        }

        $checks = collect($healthyChecks)
            ->map(function (string $checkName) {
                return new Check([
                    'type' => $checkName,
                    'status' => CheckStatus::SUCCESS,
                    'last_run_message' => 'check-message',
                    'custom_properties' => [],
                ]);
            })->merge(
                collect($unhealthyChecks)
                    ->map(function (string $checkName) {
                        return new Check([
                            'type' => $checkName,
                            'status' => CheckStatus::FAILED,
                            'custom_properties' => [],
                        ]);
                    })
            );

        collect($hostNames)->each(function ($name) use ($checks) {
            Host::create([
                'name' => $name,
                'ssh_user' => 'user',
                'port' => 22,
            ])->checks()->saveMany($checks);
        });
    };
});

it('displays a message when no hosts are configures', function () {
    Artisan::call('server-monitor:list-checks');

    $this->dontSeeInConsoleOutput(['Host', 'Check']);

    $this->seeInConsoleOutput('There are no hosts configured');
});

it('displays a list of hosts with checks', function () {
    ($this->addHosts)('test-host', ['healthy-check'], ['unhealthy-check']);

    Artisan::call('server-monitor:list-checks');

    $this->seeInConsoleOutput(['Unhealthy checks', 'Healthy checks', ' healthy-check', 'unhealthy-check']);

    $this->dontSeeInConsoleOutput(['There are no hosts configured']);
});

it('displays a list of hosts with healthy checks', function () {
    ($this->addHosts)('test-host', ['check-1', 'check-2']);

    Artisan::call('server-monitor:list-checks');

    $this->seeInConsoleOutput(['Healthy checks', 'check-1', 'check-2']);

    $this->dontSeeInConsoleOutput(['There are no hosts configured', 'Unhealthy checks']);
});

it('displays a list of hosts with unhealthy checks', function () {
    ($this->addHosts)('test-host', [], ['check-1', 'check-2']);

    Artisan::call('server-monitor:list-checks');

    $this->seeInConsoleOutput(['Unhealthy checks', 'check-1', 'check-2']);

    $this->dontSeeInConsoleOutput(['There are no hosts configured', 'Healthy checks']);
});

it('displays messages for checks', function () {
    ($this->addHosts)('test-host', ['check']);

    Artisan::call('server-monitor:list-checks');

    $this->seeInConsoleOutput('check-message');
});

it('displays last check for checks', function () {
    ($this->addHosts)('test-host', ['check']);

    Artisan::call('server-monitor:list-checks');

    $this->seeInConsoleOutput('Did not run yet');
});

it('displays next check for checks', function () {
    ($this->addHosts)('test-host', ['check']);

    Artisan::call('server-monitor:list-checks');

    $this->seeInConsoleOutput('As soon as possible');
});

it('filters checks by hostname', function () {
    ($this->addHosts)('host-1', ['host-1-check']);
    ($this->addHosts)('host-2', ['host-2-check']);

    Artisan::call('server-monitor:list-checks', ['--host' => 'host-1']);

    $this->seeInConsoleOutput('host-1-check');

    $this->dontSeeInConsoleOutput('host-2-check');
});

it('filters checks by check type', function () {
    ($this->addHosts)('host-1', ['check-1', 'check-2']);
    ($this->addHosts)('host-2', ['check-2', 'check-3']);

    Artisan::call('server-monitor:list-checks', ['--check' => 'check-1']);

    $this->seeInConsoleOutput(['check-1', 'host-1']);

    $this->dontSeeInConsoleOutput(['check-2', 'check-3', 'host-2']);
});

it('can work with check that already have run', function () {
    ($this->addHosts)('host-1', ['check-1']);

    $check = Check::first();

    $check->last_ran_at = Carbon::now()->subMinutes(10);
    $check->next_run_in_minutes = 5;
    $check->save();

    Artisan::call('server-monitor:list-checks');

    $this->seeInConsoleOutput('As soon as possible');

    $check->last_ran_at = Carbon::now()->subMinutes(10);
    $check->next_run_in_minutes = 15;
    $check->save();

    Artisan::call('server-monitor:list-checks');

    $this->seeInConsoleOutput('5 minutes from now');
});
