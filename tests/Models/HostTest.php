<?php

namespace Spatie\ServerMonitor\Test\Models;

use Spatie\ServerMonitor\Models\Host;
use Spatie\ServerMonitor\Models\Check;
use Spatie\ServerMonitor\Test\TestCase;
use Spatie\ServerMonitor\Models\Enums\HostHealth;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;

class HostTest extends TestCase
{
    /** @test */
    public function its_status_will_be_a_warning_when_it_has_no_checks()
    {
        $host = $this->createHostWithChecks([]);

        $this->assertTrue($host->status === HostHealth::WARNING);
    }

    /** @test */
    public function it_will_determine_that_it_is_healthy_when_all_its_checks_have_succeeded()
    {
        $host = $this->createHostWithChecks([
            CheckStatus::SUCCESS,
        ]);

        $this->assertTrue($host->status === HostHealth::HEALTHY);
    }

    /** @test */
    public function it_will_determine_that_it_is_unhealthy_when_one_of_its_checks_has_failed()
    {
        $host = $this->createHostWithChecks([
            CheckStatus::SUCCESS, CheckStatus::FAILED, CheckStatus::WARNING, CheckStatus::NOT_YET_CHECKED,
        ]);

        $this->assertTrue($host->status === HostHealth::UNHEALTHY);
    }

    /** @test */
    public function its_status_will_be_a_warning_when_it_contains_an_check_that_has_not_run_yet()
    {
        $host = $this->createHostWithChecks([
            CheckStatus::SUCCESS, CheckStatus::NOT_YET_CHECKED,
        ]);

        $this->assertTrue($host->status === HostHealth::WARNING);
    }

    /** @test */
    public function its_status_will_be_a_warning_when_it_contains_an_check_that_issued_a_warning()
    {
        $host = $this->createHostWithChecks([
            CheckStatus::SUCCESS, CheckStatus::WARNING,
        ]);

        $this->assertTrue($host->status === HostHealth::WARNING);
    }

    /** @test */
    public function it_has_helper_methods_to_determine_its_status()
    {
        $host = $this->createHostWithChecks([
            CheckStatus::SUCCESS,
        ]);

        $this->assertTrue($host->isHealthy());
        $this->assertFalse($host->isUnhealthy());
        $this->assertFalse($host->hasWarning());

        $host = $this->createHostWithChecks([
            CheckStatus::WARNING,
        ]);

        $this->assertFalse($host->isHealthy());
        $this->assertFalse($host->isUnhealthy());
        $this->assertTrue($host->hasWarning());

        $host = $this->createHostWithChecks([
            CheckStatus::FAILED,
        ]);

        $this->assertFalse($host->isHealthy());
        $this->assertTrue($host->isUnhealthy());
        $this->assertFalse($host->hasWarning());
    }

    protected function createHostWithChecks(array $statuses): Host
    {
        $host = Host::create([
            'name' => 'hostname',
        ]);

        $host->checks()->saveMany(collect($statuses)->map(function (string $status) {
            return new Check([
                'type' => 'my-check-'.rand(),
                'status' => $status,
            ]);
        }));

        return $host;
    }
}
