<?php

namespace Spatie\ServerMonitor\CheckDefinitions\Test;

use Exception;
use Spatie\ServerMonitor\CheckDefinitions\CheckDefinition;
use Spatie\ServerMonitor\CheckDefinitions\Diskspace;
use Spatie\ServerMonitor\Models\Check;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;
use Spatie\ServerMonitor\Test\TestCase;
use Symfony\Component\Process\Process;

class CheckDefinitionTest extends TestCase
{
    /** @var \Spatie\ServerMonitor\CheckDefinitions\Diskspace */
    protected $diskspaceCheckDefinition;

    /** @var \Spatie\ServerMonitor\Models\Check */
    protected $check;

    public function setUp(): void
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

        $this->diskspaceCheckDefinition->determineResult($process);

        $this->check->fresh();

        $this->assertEquals(CheckStatus::FAILED, $this->check->status);
    }

    /** @test */
    public function it_will_mark_the_check_as_failed_when_a_check_definition_throws_an_exception()
    {
        $checkDefinition = new class extends CheckDefinition {
            public function resolve(Process $process)
            {
                throw new Exception('my exception message');
            }

            public function performNextRunInMinutes(): int
            {
                return 0;
            }
        };

        $checkDefinition->setCheck($this->check);

        $process = $this->getSuccessfulProcessWithOutput();

        $checkDefinition->determineResult($process);

        $this->check->fresh();

        $this->assertEquals(CheckStatus::FAILED, $this->check->status);

        $this->assertEquals('Exception occurred: my exception message', $this->check->last_run_message);
    }
}
