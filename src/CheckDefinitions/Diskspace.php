<?php

namespace Spatie\ServerMonitor\CheckDefinitions;

use Carbon\Carbon;
use Symfony\Component\Process\Process;

class Diskspace extends CheckDefinition
{
    public function getCommand(): string
    {
        return 'df .';
    }

    public function handleFinishedProcess(Process $process)
    {
        $percentage = $this->getFreeSpacePercentage($process->getOutput());

        if ($percentage > 90) {
            $this->check->failed("Disk nearly full: {$percentage}");

            return;
        }

        if ($percentage > 80) {
            $this->check->warn("Free diskspace running low: {$percentage}");

            return;
        }

        $this->check->succeeded();

        return;
    }

    public function performNextRunAt(): Carbon
    {
        return Carbon::now()->addMinutes(5);
    }

    protected function getFreeSpacePercentage(string $commandOutput): int
    {
        return 80;
    }
}