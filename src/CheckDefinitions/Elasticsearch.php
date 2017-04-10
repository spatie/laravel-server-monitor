<?php

namespace Spatie\ServerMonitor\CheckDefinitions;

use Symfony\Component\Process\Process;

class Elasticsearch extends CheckDefinition
{
    public $command = 'curl --silent http://localhost:9200';

    public function resolve(Process $process)
    {
        $checkSucceeded = str_contains($process->getOutput(), 'lucene_version');

        if ($checkSucceeded) {
            $this->check->succeed('is running');

            return;
        }

        $this->check->fail('is not running');
    }
}
