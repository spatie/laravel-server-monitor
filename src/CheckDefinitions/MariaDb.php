<?php

namespace Spatie\ServerMonitor\CheckDefinitions;

use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class MariaDb extends CheckDefinition
{
    public $command = 'ps -e | grep mariadbd$';

    public function resolve(Process $process)
    {
        if (Str::contains($process->getOutput(), 'mariadb')) {
            $this->check->succeed('is running');

            return;
        }

        $this->check->fail('is not running');
    }
}
