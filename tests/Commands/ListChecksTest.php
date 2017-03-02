<?php

namespace Spatie\ServerMonitor\Test\Commands;

use Artisan;
use Carbon\Carbon;
use Spatie\ServerMonitor\Models\Host;
use Spatie\ServerMonitor\Models\Check;
use Spatie\ServerMonitor\Test\TestCase;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;

class ListChecksTest extends TestCase
{
    /** @test */
    public function it_displays_a_message_when_no_hosts_are_configures()
    {
        Artisan::call('server-monitor:list-checks');

        $this->dontSeeInConsoleOutput(['Host', 'Check']);

        $this->seeInConsoleOutput('There are no hosts configured');
    }

    /** @test */
    public function it_displays_a_list_of_hosts_with_checks()
    {
        $this->addHosts('test-host', ['healthy-check'], ['unhealthy-check']);

        Artisan::call('server-monitor:list-checks');

        $this->seeInConsoleOutput(['Unhealthy checks', 'Healthy checks', ' healthy-check', 'unhealthy-check']);

        $this->dontSeeInConsoleOutput(['There are no hosts configured']);
    }

    /** @test */
    public function it_displays_a_list_of_hosts_with_healthy_checks()
    {
        $this->addHosts('test-host', ['check-1', 'check-2']);

        Artisan::call('server-monitor:list-checks');

        $this->seeInConsoleOutput(['Healthy checks', 'check-1', 'check-2']);

        $this->dontSeeInConsoleOutput(['There are no hosts configured', 'Unhealthy checks']);
    }

    /** @test */
    public function it_displays_a_list_of_hosts_with_unhealthy_checks()
    {
        $this->addHosts('test-host', [], ['check-1', 'check-2']);

        Artisan::call('server-monitor:list-checks');

        $this->seeInConsoleOutput(['Unhealthy checks', 'check-1', 'check-2']);

        $this->dontSeeInConsoleOutput(['There are no hosts configured', 'Healthy checks']);
    }

    /** @test */
    public function it_displays_messages_for_checks()
    {
        $this->addHosts('test-host', ['check']);

        Artisan::call('server-monitor:list-checks');

        $this->seeInConsoleOutput('check-message');
    }

    /** @test */
    public function it_displays_last_check_for_checks()
    {
        $this->addHosts('test-host', ['check']);

        Artisan::call('server-monitor:list-checks');

        $this->seeInConsoleOutput('Did not run yet');
    }

    /** @test */
    public function it_displays_next_check_for_checks()
    {
        $this->addHosts('test-host', ['check']);

        Artisan::call('server-monitor:list-checks');

        $this->seeInConsoleOutput('As soon as possible');
    }

    /** @test */
    public function it_filters_checks_by_hostname()
    {
        $this->addHosts('host-1', ['host-1-check']);
        $this->addHosts('host-2', ['host-2-check']);

        Artisan::call('server-monitor:list-checks', ['--host' => 'host-1']);

        $this->seeInConsoleOutput('host-1-check');

        $this->dontSeeInConsoleOutput('host-2-check');
    }

    /** @test */
    public function it_filters_checks_by_check_type()
    {
        $this->addHosts('host-1', ['check-1', 'check-2']);
        $this->addHosts('host-2', ['check-2', 'check-3']);

        Artisan::call('server-monitor:list-checks', ['--check' => 'check-1']);

        $this->seeInConsoleOutput(['check-1', 'host-1']);

        $this->dontSeeInConsoleOutput(['check-2', 'check-3', 'host-2']);
    }

    /** @test */
    public function it_can_work_with_check_that_already_have_run()
    {
        $this->addHosts('host-1', ['check-1']);

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
    }

    public function addHosts($hostNames, array $healthyChecks = [], array $unhealthyChecks = [])
    {
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
            })->merge(collect($unhealthyChecks)
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
    }
}
