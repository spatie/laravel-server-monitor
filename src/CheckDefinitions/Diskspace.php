<?php

namespace Spatie\ServerMonitor\CheckDefinitions;

use Spatie\Regex\Regex;
use Symfony\Component\Process\Process;

final class Diskspace extends CheckDefinition
{
    public $command = 'df -P .';

    public function resolve(Process $process)
    {
        $percentage = $this->getDiskUsagePercentage($process->getOutput());

        $message = "usage at {$percentage}%";

        if ($percentage >= 90) {
            $this->check->fail($message);

            return;
        }

        if ($percentage >= 80) {
            $this->check->warn($message);

            return;
        }

        $this->check->succeed($message);
    }

    protected function getDiskUsagePercentage(string $commandOutput): int
    {
        return (int) Regex::match('/(\d?\d)%/', $commandOutput)->group(1);
    }
}
