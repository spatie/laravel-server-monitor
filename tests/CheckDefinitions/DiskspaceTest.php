<?php

namespace Spatie\ServerMonitor\CheckDefinitions\Test;

use Spatie\ServerMonitor\Models\Check;
use Spatie\ServerMonitor\Test\TestCase;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;
use Spatie\ServerMonitor\CheckDefinitions\Diskspace;

class DiskspaceTest extends TestCase
{
    /** @var \Spatie\ServerMonitor\CheckDefinitions\Diskspace */
    protected $diskspaceCheckDefinition;

    /** @var \Spatie\ServerMonitor\Models\Check */
    protected $check;

    public function setUp()
    {
        parent::setUp();

        $this->createHost('localhost', 65000, ['diskspace']);

        $this->check = Check::first();

        $this->diskspaceCheckDefinition = (new Diskspace())->setCheck($this->check);
    }

    /**
     * @test
     *
     * @dataProvider percentageProvider
     */
    public function it_can_handle_its_command_output(int $diskspaceUsed, string $status)
    {
        $process = $this->getSuccessfulProcessWithOutput(
            'Filesystem 512-blocks      Used Available Capacity  Mounted on\n'.
            "/dev/disk1  974700800 830137776 144051024   {$diskspaceUsed}%    /"
        );

        $this->diskspaceCheckDefinition->resolve($process);

        $this->check->fresh();

        $this->assertStringContains("{$diskspaceUsed}%", $this->check->last_run_message);
        $this->assertEquals($status, $this->check->status);
    }

    public function percentageProvider()
    {
        return [
            [40, CheckStatus::SUCCESS],
            [50, CheckStatus::SUCCESS],
            [79, CheckStatus::SUCCESS],
            [80, CheckStatus::WARNING],
            [89, CheckStatus::WARNING],
            [90, CheckStatus::FAILED],
            [95, CheckStatus::FAILED],
        ];
    }
}
