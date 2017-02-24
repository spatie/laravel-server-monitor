<?php

namespace Spatie\ServerMonitor\CheckDefinitions;

use Symfony\Component\Process\Process;

final class MySql extends CheckDefinition
{
    public $command = 'ps -e | grep mysql';

    public function handleSuccessfulProcess(Process $process)
    {
        if (str_contains($process->getOutput(), 'mysql')) {
            $this->check->succeeded('is running');

            return;
        };

        $this->check->failed('is not running');
    }

    public function performNextRunInMinutes(): int
    {
        return 5;
    }
}
