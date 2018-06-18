<?php

namespace Spatie\ServerMonitor\Test\Excluded;

use Spatie\ServerMonitor\Excluded\ExcludedErrors;
use Spatie\ServerMonitor\Models\Check;
use Symfony\Component\Process\Process;
use Spatie\ServerMonitor\Test\TestCase;
use Spatie\ServerMonitor\Manipulators\Manipulator;

class ExcludedErrorTest extends TestCase
{

    /** @test */
    public function testExcludedError()
    {

        //pretending like a error comes from SSH connection because i dont know how to emulate this situation
        $error = "reverse mapping checking getaddrinfo for 192-168-1-243.foo.bar.net failed - POSSIBLE BREAK-IN ATTEMPT!";



        if(ExcludedErrors::hasExcludedError($error))
        {
            $this->assertTrue(true);
            return;
        }

        $this->assertTrue(false);

    }

}