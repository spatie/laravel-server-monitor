<?php

namespace Spatie\ServerMonitor\CheckDefinitions;

use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class Memcached extends CheckDefinition
{
    public $command = 'service memcached status';

    public function resolve(Process $process)
    {
        if (Str::contains($process->getOutput(), ['memcached is running', 'active (running)'])) {
            $this->check->succeed('is running');

            return;
        }

        $this->check->fail('is not running');
    }
}
