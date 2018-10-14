<?php

namespace Spatie\ServerMonitor\CheckDefinitions;

use Spatie\Regex\Regex;
use Symfony\Component\Process\Process;

class Diskspace extends CheckDefinition
{
    public $command = 'df -P .';

    public function resolve(Process $process)
    {
        $percentage = $this->getDiskUsagePercentage($process->getOutput());

        $message = "usage at {$percentage}%";

        $thresholds = config('server-monitor.diskspace_percentage_threshold', [
            'warning' => 80,
            'fail' => 90,
        ]);

        if ($percentage >= $thresholds['fail']) {
            $this->check->fail($message);

            return;
        }

        if ($percentage >= $thresholds['warning']) {
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
