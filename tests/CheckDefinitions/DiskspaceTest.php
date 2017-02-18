<?php

namespace Spatie\ServerMonitor\CheckDefinitions\Test;

use Mockery;
use Spatie\ServerMonitor\CheckDefinitions\Diskspace;
use Spatie\ServerMonitor\Models\Check;
use Spatie\ServerMonitor\Test\TestCase;
use Symfony\Component\Process\Process;

class DiskspaceTest extends TestCase
{
    /** @var \Spatie\ServerMonitor\CheckDefinitions\Diskspace */
    protected $diskspaceCheckDefinition;

    public function setUp()
    {
        parent::setUp();

        $this->process = Mockery::mock(Process::class);

        $this->createHost('localhost', 65000, ['diskspace']);

        $this->check = Check::first();

        $this->diskspaceCheckDefinition = (new Diskspace())->setCheck($this->check);
    }

    /** @test */
    public function it_tests_diskspace()
    {
        $process = Mockery::mock(Process::class)
            ->shouldReceive('getOutput')
            ->andReturn('blabla');

        $process->shouldReceive('stop')
            ->andReturn('blabla');

        $this->diskspaceCheckDefinition->handleSuccessfulProcess($process);


    }


}