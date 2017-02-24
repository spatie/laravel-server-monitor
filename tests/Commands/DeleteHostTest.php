<?php

namespace Spatie\ServerMonitor\Test\Commands;

use Artisan;
use Spatie\ServerMonitor\Models\Host;
use Spatie\ServerMonitor\Test\TestCase;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;

class DeleteHostTest extends TestCase
{
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

        Artisan::call('server-monitor:delete-host', ['name' => 'test-host']);

        $this->seeInConsoleOutput('test-host deleted!');

        $host = Host::where('name', 'test-host')->first();

        $this->assertEmpty($host, 'Host was not deleted');
    }
}
