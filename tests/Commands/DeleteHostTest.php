<?php

namespace Spatie\ServerMonitor\Test\Commands;

use Artisan;
use Mockery as m;
use Spatie\ServerMonitor\Models\Host;
use Spatie\ServerMonitor\Test\TestCase;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;

class DeleteHostTest extends TestCase
{
    /** @var m\MockInterface */
    protected $command;

    public function setUp()
    {
        parent::setUp();

        $this->command = m::mock('Spatie\ServerMonitor\Commands\DeleteHost[confirm]');

        $this->app->bind('command.server-monitor:delete-host', function () {
            return $this->command;
        });
    }

    /** @test */
    public function it_can_delete_hosts()
    {
        Host::create([
            'name' => 'test-host',
            'ssh_user' => 'user',
            'port' => 22,
        ])->checks()->create([
            'type' => 'test-check',
            'status' => CheckStatus::NOT_YET_CHECKED,
            'custom_properties' => [],
        ]);

        $this->command
            ->shouldReceive('confirm')
            ->once()
            ->with('/Are you sure you wish to delete `test-host`?/')
            ->andReturn('y');

        Artisan::call('server-monitor:delete-host', ['name' => 'test-host']);

        $host = Host::where('name', 'test-host')->first();

        $this->seeInConsoleOutput('Host `test-host` was deleted');

        $this->assertEmpty($host);
    }

    /** @test */
    public function it_can_stop_deleting_a_host()
    {
        Host::create([
            'name' => 'test-host',
            'ssh_user' => 'user',
            'port' => 22,
        ])->checks()->create([
            'type' => 'test-check',
            'status' => CheckStatus::NOT_YET_CHECKED,
            'custom_properties' => [],
        ]);

        $this->command
            ->shouldReceive('confirm')
            ->once()
            ->with('/Are you sure you wish to delete `test-host`?/')
            ->andReturn('');

        Artisan::call('server-monitor:delete-host', ['name' => 'test-host']);

        $host = Host::where('name', 'test-host')->first();

        $this->dontSeeInConsoleOutput('Host `test-host` was deleted');

        $this->assertInstanceOf(Host::class, $host);
    }
}
