<?php

namespace Spatie\ServerMonitor\Test\Models\Concerns;

use Spatie\ServerMonitor\Models\Host;
use Spatie\ServerMonitor\Models\Check;
use Spatie\ServerMonitor\Test\TestCase;

class HasProcessTest extends TestCase
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
    public function it_uses_the_host_in_the_process_command()
    {
        $this->check->getProcessCommand();

        $this->assertStringStartsWith("ssh my-host   'bash", $this->check->getProcessCommand());
    }

    /** @test */
    public function it_uses_its_host_custom_port_in_the_process_command()
    {
        tap($this->check->host, function (Host $host) {
            $host->port = 123;
            $host->save();
        });

        $this->check->getProcessCommand();

        $this->assertStringStartsWith("ssh my-host -p 123  'bash", $this->check->getProcessCommand());
    }

    /** @test */
    public function it_uses_its_host_custom_ssh_user_in_the_process_command()
    {
        tap($this->check->host, function (Host $host) {
            $host->ssh_user = 'my-ssh-user';
            $host->save();
        });

        $this->check->getProcessCommand();

        $this->assertStringStartsWith("ssh my-ssh-user@my-host   'bash", $this->check->getProcessCommand());
    }

    /** @test */
    public function it_will_use_the_ip_address_instead_of_the_host_name()
    {
        tap($this->check->host, function (Host $host) {
            $host->ip = '1.2.3.4';
            $host->save();
        });

        $this->check->getProcessCommand();

        $this->assertStringStartsWith("ssh 1.2.3.4   'bash", $this->check->getProcessCommand());
    }

    /** @test */
    public function it_will_use_the_prefix_specified_in_the_config_file()
    {
        $prefix = '-q';

        $this->app['config']->set('server-monitor.ssh_command_prefix', $prefix);

        tap($this->check->host, function (Host $host) {
            $host->ip = '1.2.3.4';
            $host->save();
        });

        $this->check->getProcessCommand();

        $this->assertStringStartsWith("ssh {$prefix} 1.2.3.4   'bash", $this->check->getProcessCommand());
    }

    /** @test */
    public function it_will_use_the_suffix_specified_in_the_config_file()
    {
        $suffix = '-o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no';

        $this->app['config']->set('server-monitor.ssh_command_suffix', $suffix);

        tap($this->check->host, function (Host $host) {
            $host->ip = '1.2.3.4';
            $host->save();
        });

        $this->check->getProcessCommand();

        $this->assertStringStartsWith("ssh 1.2.3.4  {$suffix} 'bash", $this->check->getProcessCommand());
    }
}
