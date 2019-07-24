<?php

namespace Spatie\ServerMonitor\CheckDefinitions;

use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class MySql extends CheckDefinition
{
    public $command = 'ps -e | grep mysqld$';

    public function resolve(Process $process)
    {
        if (Str::contains($process->getOutput(), 'mysql')) {
            $this->check->succeed('is running');

            return;
        }

        $this->check->fail('is not running');
    }
}
