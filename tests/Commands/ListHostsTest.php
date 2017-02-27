<?php

namespace Spatie\ServerMonitor\Test\Commands;

use Artisan;
use Spatie\ServerMonitor\Models\Host;
use Spatie\ServerMonitor\Models\Check;
use Spatie\ServerMonitor\Test\TestCase;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;

class ListHostsTest extends TestCase
{
    /** @test */
    public function it_displays_a_message_when_no_hosts_are_configures()
    {
        Artisan::call('server-monitor:list-hosts');

        $this->dontSeeInConsoleOutput(['Checks', 'Health']);

        $this->seeInConsoleOutput('There are no hosts configured');
    }

    /** @test */
    public function it_displays_a_list_of_all_hosts()
    {
        $testHosts = ['test-host-1', 'test-host-2'];

        $this->addHosts($testHosts);

        Artisan::call('server-monitor:list-hosts');

        $this->dontSeeInConsoleOutput('There are no hosts configured');

        $this->seeInConsoleOutput($testHosts);
    }

    /** @test */
    public function it_filters_all_hosts_by_hostname()
    {
        $testHosts = ['test-host-1', 'test-host-2'];

        $this->addHosts($testHosts);

        Artisan::call('server-monitor:list-hosts', ['--host' => $testHosts[0]]);

        $this->seeInConsoleOutput($testHosts[0]);

        $this->dontSeeInConsoleOutput($testHosts[1]);
    }

    /** @test */
    public function it_filters_all_hosts_by_checktype()
    {
        $this->addHosts('test-host', ['correct-check', 'wrong-check']);

        Artisan::call('server-monitor:list-hosts', ['--check' => 'correct-check']);

        $this->dontSeeInConsoleOutput('wrong-check');

        $this->seeInConsoleOutput('correct-check');
    }

    /** @test */
    public function it_filters_all_hosts_by_hostname_and_checktype()
    {
        $this->addHosts('wrong-host');
        $this->addHosts('correct-host', ['wrong-check', 'correct-check']);

        Artisan::call('server-monitor:list-hosts', [
            '--host' => 'correct-host',
            '--check' => 'correct-check',
        ]);

        $this->dontSeeInConsoleOutput(['wrong-host', 'wrong-check']);

        $this->seeInConsoleOutput(['correct-check', 'correct-host']);
    }

    public function addHosts($names, array $checks = [])
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
    }
}
