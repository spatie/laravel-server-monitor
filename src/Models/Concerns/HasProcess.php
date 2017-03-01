<?php

namespace Spatie\ServerMonitor\Models\Concerns;

use Symfony\Component\Process\Process;
use Spatie\ServerMonitor\Manipulators\Manipulator;

trait HasProcess
{
    public function getProcess(): Process
    {
        static $processes = [];

        if (! isset($processes[$this->id])) {
            $process = new Process($this->getProcessCommand());

            $process->setTimeout($this->getDefinition()->timeoutInSeconds());

            $manipulator = app(Manipulator::class);

            $process = $manipulator->manipulateProcess($process, $this);

            $processes[$this->id] = $process;
        }

        return $processes[$this->id];
    }

    public function getProcessCommand(): string
    {
        $delimiter = 'EOF-LARAVEL-SERVER-MONITOR';

        $definition = $this->getDefinition();

        $portArgument = empty($this->host->port) ? '' : "-p {$this->host->port}";

        $sshCommandSuffix = config('server-monitor.ssh_command_suffix');

        return "ssh {$this->getTarget()} {$portArgument} {$sshCommandSuffix} 'bash -se <<$delimiter".PHP_EOL
            .'set -e'.PHP_EOL
            .$definition->command().PHP_EOL
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
