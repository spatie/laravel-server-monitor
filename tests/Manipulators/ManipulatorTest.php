<?php

namespace Spatie\ServerMonitor\Test\Manipulators;

use Spatie\ServerMonitor\Models\Check;
use Symfony\Component\Process\Process;
use Spatie\ServerMonitor\Test\TestCase;
use Spatie\ServerMonitor\Manipulators\Manipulator;

class PassThroughTest extends TestCase
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
    public function it_will_manipulate_the_process_of_a_check()
    {
        $manipulator = new class implements Manipulator {
            public function manipulateProcess(Process $process, Check $check): Process
            {
                $process->setCommandLine('modified');

                return $process;
            }
        };

        $this->app->bind(Manipulator::class, get_class($manipulator));

        $this->assertEquals('modified', $this->check->getProcess()->getCommandLine());
    }
}
