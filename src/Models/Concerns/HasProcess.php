<?php

namespace Spatie\ServerMonitor\Models\Concerns;

use Symfony\Component\Process\Process;
use Spatie\ServerMonitor\Manipulators\Manipulator;

trait HasProcess
{
    public function getProcess(): Process
    {
        return blink()->once("process.{$this->id}", function () {
            $process = new Process($this->getProcessCommand());

            $process->setTimeout($this->getDefinition()->timeoutInSeconds());

            $manipulator = app(Manipulator::class);

            return $manipulator->manipulateProcess($process, $this);
        });
    }

    public function getProcessCommand(): string
    {
        $delimiter = 'EOF-LARAVEL-SERVER-MONITOR';

        $definition = $this->getDefinition();

        $portArgument = empty($this->host->port) ? '' : "-p {$this->host->port}";

        $sshCommandPrefix = config('server-monitor.ssh_command_prefix');
        $sshCommandSuffix = config('server-monitor.ssh_command_suffix');

        return "ssh {$sshCommandPrefix} {$this->getTarget()} {$portArgument} {$sshCommandSuffix} 'bash -se <<$delimiter".PHP_EOL
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
