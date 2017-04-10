<?php

namespace Spatie\ServerMonitor\CheckDefinitions;

use Symfony\Component\Process\Process;

class MySql extends CheckDefinition
{
    public $command = 'ps -e | grep mysqld$';

    public function resolve(Process $process)
    {
        if (str_contains($process->getOutput(), 'mysql')) {
            $this->check->succeed('is running');

            return;
        }

        $this->check->fail('is not running');
    }
}
