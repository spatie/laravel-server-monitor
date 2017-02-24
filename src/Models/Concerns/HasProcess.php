<?php

namespace Spatie\ServerMonitor\Models\Concerns;

use Symfony\Component\Process\Process;

trait HasProcess
{
    public function getProcess(): Process
    {
        static $processes = [];

        if (! isset($processes[$this->id])) {
            $processes[$this->id] = new Process($this->getProcessCommand());
        }

        return $processes[$this->id];
    }

    public function getProcessCommand(): string
    {
        $delimiter = 'EOF-LARAVEL-SERVER-MONITOR';

        $definition = $this->getDefinition();

        $portArgument = empty($this->host->port) ? '' : "-p {$this->host->port}";

        return "ssh {$this->getTarget()} {$portArgument} 'bash -se <<$delimiter".PHP_EOL
            .'set -e'.PHP_EOL
            .$definition->getCommand().PHP_EOL
            .$delimiter."'";
    }

    protected function getTarget(): string
    {
        $target = empty($this->host->ip)
            ? $this->host->name
            : $this->host->ip;

        if ($this->host->ssh_user) {
            $target = $this->host->ssh_user.'@'.$target;
        }

        return $target;
    }
}