<?php

namespace Spatie\ServerMonitor\CheckDefinitions;

use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class Elasticsearch extends CheckDefinition
{
    public $command = 'curl --silent http://localhost:9200';

    public function command(): string
    {
        $command = $this->command;

        $customIp = $this->check->getCustomProperty('ip');

        if (! empty($customIp)) {
            $command = str_replace('localhost', $customIp, $command);
        }

        return $command;
    }

    public function resolve(Process $process)
    {
        $checkSucceeded = Str::contains($process->getOutput(), 'lucene_version');

        if ($checkSucceeded) {
            $this->check->succeed('is running');

            return;
        }

        $this->check->fail('is not running');
    }
}
