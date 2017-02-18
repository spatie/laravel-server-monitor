<?php

namespace Spatie\ServerMonitor\CheckDefinitions;

use Spatie\Regex\Regex;
use Symfony\Component\Process\Process;

final class Diskspace extends CheckDefinition
{
    public $command = 'df -P .';

    public function handleSuccessfulProcess(Process $process)
    {
        $percentage = $this->getDiskUsagePercentage($process->getOutput());

        if ($percentage >= 90) {
            $this->check->failed("Disk nearly full: {$percentage}%");

            return;
        }

        if ($percentage >= 80) {
            $this->check->warn("The disk space usage is now at {$percentage}%");

            return;
        }

        $this->check->succeeded("The disk space usage is now at {$percentage}%");

        return;
    }

    public function performNextRunInMinutes(): int
    {
        return 10;
    }

    protected function getDiskUsagePercentage(string $commandOutput): int
    {
        return (int) Regex::match('/(\d?\d)%/', $commandOutput)->group(1);
    }
}
