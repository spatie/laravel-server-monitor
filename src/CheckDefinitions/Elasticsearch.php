<?php

namespace Spatie\ServerMonitor\CheckDefinitions;

use Spatie\Regex\Regex;
use Symfony\Component\Process\Process;

final class Elasticsearch extends CheckDefinition
{
    public function getCommand(): string
    {
        return 'curl http://localhost:9200';
    }

    public function handleFinishedProcess(Process $process)
    {
        $checkSucceeded = str_contains($process->getOutput(), 'lucene_version');

        if ($checkSucceeded) {
            $this->check->failed("Elasticsearch is down");

            return;
        }

        $this->check->succeeded("Elasticsearch is up");

        return;
    }

    public function performNextRunInMinutes(): int
    {
        return 0;
    }

    protected function getDiskUsagePercentage(string $commandOutput): int
    {
        return (int) Regex::match('/(\d?\d)%/', $commandOutput)->group(1);
    }
}
