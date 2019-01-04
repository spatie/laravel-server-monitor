<?php

namespace Spatie\ServerMonitor\CheckDefinitions;

use Symfony\Component\Process\Process;

class Redis extends CheckDefinition
{
    public $command = 'redis-cli ping';

    public function resolve(Process $process)
    {
        if ('PONG' == trim($process->getOutput())) {
            $this->check->succeed('is running');

            return;
        }

        $this->check->fail('is not running');
    }
}
