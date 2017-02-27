<?php

namespace Spatie\ServerMonitor\Test\Commands;

use Artisan;
use Mockery as m;
use Spatie\ServerMonitor\Models\Host;
use Spatie\ServerMonitor\Test\TestCase;
use Spatie\ServerMonitor\Commands\AddHost;

class AddHostTest extends TestCase
{
    /** @var \Spatie\ServerMonitor\Commands\AddHost|m\Mock */
    protected $command;

    public function setUp()
    {
        parent::setUp();

        $this->command = m::mock('Spatie\ServerMonitor\Commands\AddHost[ask, confirm, choice]');

        $this->app->bind('command.server-monitor:add-host', function () {
            return $this->command;
        });
    }

    /** @test */
    public function it_can_add_a_host_with_all_checks()
    {
        $this->command
            ->shouldReceive('ask')
            ->once()
            ->with('/What is the name of the host/')
            ->andReturn('myhost-com');

        $this->command
            ->shouldReceive('confirm')
            ->once()
            ->with('/Should a custom ssh user be used/')
            ->andReturn('');

        $this->command
            ->shouldReceive('confirm')
            ->once()
            ->with('/Should a custom port be used/')
            ->andReturn('');

        $this->command
            ->shouldReceive('confirm')
            ->once()
            ->with('/Should a specific ip address be used/')
            ->andReturn('');

        $this->command
            ->shouldReceive('choice')
            ->once()
            ->andReturn([AddHost::$allChecksLabel]);

        Artisan::call('server-monitor:add-host');

        $host = Host::first();

        $this->assertEquals('myhost-com', $host->name);

        $this->assertEquals(count(config('server-monitor.checks')), count($host->checks->pluck('type')));

        foreach (array_keys(config('server-monitor.checks')) as $checkType) {
            $this->assertTrue(in_array($checkType, $host->checks->pluck('type')->toArray()));
        }
    }

    /** @test */
    public function it_can_add_a_host_with_specific_checks()
    {
        $this->command
            ->shouldReceive('ask')
            ->once()
            ->with('/What is the name of the host/')
            ->andReturn('myhost-com');

        $this->command
            ->shouldReceive('confirm')
            ->once()
            ->with('/Should a custom ssh user be used/')
            ->andReturn('');

        $this->command
            ->shouldReceive('confirm')
            ->once()
            ->with('/Should a custom port be used/')
            ->andReturn('');

        $this->command
            ->shouldReceive('confirm')
            ->once()
            ->with('/Should a specific ip address be used/')
            ->andReturn('');

        $this->command
            ->shouldReceive('choice')
            ->once()
            ->andReturn([0 => 'diskspace']);

        Artisan::call('server-monitor:add-host');

        $host = Host::first();

        $this->assertEquals('myhost-com', $host->name);

        $this->assertCount(1, $host->checks);

        $this->assertEquals('diskspace', $host->checks->first()->type);
    }

    /** @test */
    public function it_can_add_a_host_with_a_specific_port_and_user()
    {
        $this->command
            ->shouldReceive('ask')
            ->once()
            ->with('/What is the name of the host/')
            ->andReturn('myhost-com');

        $this->command
            ->shouldReceive('confirm')
            ->once()
            ->with('/Should a custom ssh user be used/')
            ->andReturn('y');

        $this->command
            ->shouldReceive('ask')
            ->once()
            ->with('/Which user/')
            ->andReturn('my-user');

        $this->command
            ->shouldReceive('confirm')
            ->once()
            ->with('/Should a custom port be used/')
            ->andReturn('y');

        $this->command
            ->shouldReceive('confirm')
            ->once()
            ->with('/Should a specific ip address be used/')
            ->andReturn('');

        $this->command
            ->shouldReceive('ask')
            ->once()
            ->with('/Which port/')
            ->andReturn(123);

        $this->command
            ->shouldReceive('choice')
            ->once()
            ->andReturn([0 => 'diskspace']);

        Artisan::call('server-monitor:add-host');

        $host = Host::first();

        $this->assertEquals('myhost-com', $host->name);
        $this->assertEquals('my-user', $host->ssh_user);
        $this->assertEquals(123, $host->port);
    }
}
