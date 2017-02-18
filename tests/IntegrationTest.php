<?php

namespace Spatie\ServerMonitor\Test;

use Illuminate\Support\Facades\Artisan;
use Spatie\ServerMonitor\Models\Check;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;

class DiskspaceTest extends TestCase
{
    /** @var \Spatie\ServerMonitor\Models\Host */
    protected $host;

    public function setUp()
    {
        parent::setUp();

        $this->host = $this->createHost('localhost', 65000, ['diskspace']);
    }

    /** @test */
    public function it_can_run_a_successful_check()
    {
        $listenFor = 'bash -se <<EOF-LARAVEL-SERVER-MONITOR\n' .
            'set -e\n' .
            'df -P .\n' .
            'EOF-LARAVEL-SERVER-MONITOR';

        $respondWith = 'Filesystem 512-blocks      Used Available Capacity  Mounted on\n' .
            '/dev/disk1  974700800 830137776 144051024    86%    /';

        $this->server->setResponse($listenFor, $respondWith);

        Artisan::call('monitor:run-checks');

        $check = Check::where('host_id', $this->host_id)->where('type', 'diskspace')->first();

        $this->assertEquals("The disk space usage is now at 86%", $check->message);
        $this->assertEquals(CheckStatus::SUCCESS, $check->status);
    }
}