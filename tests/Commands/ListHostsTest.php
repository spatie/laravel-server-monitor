<?php

namespace Spatie\ServerMonitor\Test\Commands;

use Illuminate\Support\Facades\Artisan;
use Spatie\ServerMonitor\Models\Check;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;
use Spatie\ServerMonitor\Models\Host;

beforeEach(function () {
    $this->addHosts = function ($names, array $checks = []): void
    {
        if (! is_array($names)) {
            $names = [$names];
        }

        collect($names)->each(function ($name) use ($checks) {
            Host::create([
                'name' => $name,
                'ssh_user' => 'user',
                'port' => 22,
            ])->checks()->saveMany(collect($checks)->map(function (string $checkName) {
                return new Check([
                    'type' => $checkName,
                    'status' => CheckStatus::NOT_YET_CHECKED,
                    'custom_properties' => [],
                ]);
            }));
        });
    };
});

it('displays a message when no hosts are configures', function () {
    Artisan::call('server-monitor:list-hosts');

    $this->dontSeeInConsoleOutput(['Checks', 'Health']);

    $this->seeInConsoleOutput('There are no hosts configured');
});

it('displays a list of all hosts', function () {
    $testHosts = ['test-host-1', 'test-host-2'];

    ($this->addHosts)($testHosts);

    Artisan::call('server-monitor:list-hosts');

    $this->dontSeeInConsoleOutput('There are no hosts configured');

    $this->seeInConsoleOutput($testHosts);
});

it('filters all hosts by hostname', function () {
    $testHosts = ['test-host-1', 'test-host-2'];

    ($this->addHosts)($testHosts);

    Artisan::call('server-monitor:list-hosts', ['--host' => $testHosts[0]]);

    $this->seeInConsoleOutput($testHosts[0]);

    $this->dontSeeInConsoleOutput($testHosts[1]);
});

it('filters all hosts by checktype', function () {
    ($this->addHosts)('test-host', ['correct-check', 'wrong-check']);

    Artisan::call('server-monitor:list-hosts', ['--check' => 'correct-check']);

    $this->dontSeeInConsoleOutput('wrong-check');

    $this->seeInConsoleOutput('correct-check');
});

it('filters all hosts by hostname and checktype', function () {
    ($this->addHosts)('wrong-host');
    ($this->addHosts)('correct-host', ['wrong-check', 'correct-check']);

    Artisan::call('server-monitor:list-hosts', [
        '--host' => 'correct-host',
        '--check' => 'correct-check',
    ]);

    $this->dontSeeInConsoleOutput(['wrong-host', 'wrong-check']);

    $this->seeInConsoleOutput(['correct-check', 'correct-host']);
});
