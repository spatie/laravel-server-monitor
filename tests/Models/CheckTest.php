<?php

namespace Spatie\ServerMonitor\Test\Models;

use Carbon\Carbon;
use Spatie\ServerMonitor\Models\Host;
use Spatie\ServerMonitor\Models\Check;
use Spatie\ServerMonitor\Test\TestCase;
use Spatie\ServerMonitor\CheckDefinitions\Diskspace;
use Spatie\ServerMonitor\Exceptions\InvalidCheckDefinition;

class CheckTest extends TestCase
{
    /** @var \Spatie\ServerMonitor\Models\Check */
    protected $check;

    public function setUp()
    {
        parent::setUp();

        $this->createHost('my-host', null, ['diskspace']);

        $this->check = Check::first();
    }

    /** @test */
    public function it_can_get_its_definition()
    {
        $this->assertInstanceOf(Diskspace::class, $this->check->getDefinition());
    }

    /** @test */
    public function it_will_throw_an_exception_when_it_has_an_unkown_type()
    {
        $this->check->type = 'bla bla';
        $this->check->save();

        $this->expectException(InvalidCheckDefinition::class);

        $this->check->getDefinition();
    }

    /** @test */
    public function it_uses_the_host_in_the_process_command()
    {
        $this->check->getProcessCommand();

        $this->assertStringStartsWith("ssh my-host  'bash", $this->check->getProcessCommand());
    }

    /** @test */
    public function it_uses_its_host_custom_port_in_the_process_command()
    {
        tap($this->check->host, function (Host $host) {
            $host->port = 123;
            $host->save();
        });

        $this->check->getProcessCommand();

        $this->assertStringStartsWith("ssh my-host -p 123 'bash", $this->check->getProcessCommand());
    }

    /** @test */
    public function it_uses_its_host_custom_ssh_user_in_the_process_command()
    {
        tap($this->check->host, function (Host $host) {
            $host->ssh_user = 'my-ssh-user';
            $host->save();
        });

        $this->check->getProcessCommand();

        $this->assertStringStartsWith("ssh my-ssh-user@my-host  'bash", $this->check->getProcessCommand());
    }

    /** @test */
    public function it_will_use_the_ip_address_instead_of_the_host_name()
    {
        tap($this->check->host, function (Host $host) {
            $host->ip = '1.2.3.4';
            $host->save();
        });

        $this->check->getProcessCommand();

        $this->assertStringStartsWith("ssh 1.2.3.4  'bash", $this->check->getProcessCommand());
    }

    /** @test */
    public function it_will_determine_that_it_should_be_run()
    {
        $this->assertTrue($this->check->shouldRun());
    }

    /** @test */
    public function it_will_determine_that_it_should_not_run_when_it_is_disabled()
    {
        $this->check->enabled = false;

        $this->check->save();

        $this->assertFalse($this->check->shouldRun());
    }

    /** @test */
    public function it_will_determine_that_it_should_not_be_run_until_after_a_certain_period_of_time()
    {
        $nextRunInMinutes = 5;

        $this->check->last_ran_at = Carbon::now();

        $this->check->next_run_in_minutes = $nextRunInMinutes;

        $this->check->save();

        foreach(range(1, $nextRunInMinutes) as $pastMinutes) {

            $this->assertFalse($this->check->shouldRun());

            $this->progressMinutes(1);
        }

        $this->assertTrue($this->check->shouldRun());
    }
}
