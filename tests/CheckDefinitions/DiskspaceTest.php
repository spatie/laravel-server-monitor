<?php

namespace Spatie\ServerMonitor\CheckDefinitions\Test;

use Mockery;
use Spatie\ServerMonitor\Test\TestCase;
use Symfony\Component\Process\Process;

class DiskspaceTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->process = Mockery::mock(Process::class);
    }


}