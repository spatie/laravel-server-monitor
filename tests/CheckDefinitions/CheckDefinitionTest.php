<?php

namespace Spatie\ServerMonitor\CheckDefinitions\Test;

use Spatie\ServerMonitor\CheckDefinitions\Diskspace;
use Spatie\ServerMonitor\Models\Check;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;
use Spatie\ServerMonitor\Test\TestCase;

class CheckDefinitionTest extends TestCase
{
    /** @var \Spatie\ServerMonitor\CheckDefinitions\Diskspace */
    protected $diskspaceCheckDefinition;

    /** @var  \Spatie\ServerMonitor\Models\Check */
    protected $check;

    public function setUp()
    {
        parent::setUp();

        $this->createHost('localhost', 65000, ['diskspace']);

        $this->check = Check::first();

        $this->diskspaceCheckDefinition = (new Diskspace())->setCheck($this->check);
    }

    /** @test */
    public function it_will_mark_the_check_as_failed_when_passing_a_failed_process()
    {
        $process = $this->getFailedProcess();

        $this->diskspaceCheckDefinition->handleFinishedProcess($process);

        $this->check->fresh();

        $this->assertEquals(CheckStatus::FAILED, $this->check->status);
    }
}
