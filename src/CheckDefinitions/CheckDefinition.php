<?php

namespace Spatie\ServerMonitor\CheckDefinitions;

use Exception;
use Spatie\ServerMonitor\Models\Check;
use Symfony\Component\Process\Process;

abstract class CheckDefinition
{
    /** @var \Spatie\ServerMonitor\Models\Check */
    protected $check;

    /**
     * @param \Spatie\ServerMonitor\Models\Check $check
     *
     * @return $this
     */
    public function setCheck(Check $check)
    {
        $this->check = $check;

        return $this;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function handleFinishedProcess(Process $process)
    {
        $this->check->storeProcessOutput($process);

        try {
            if (! $process->isSuccessful()) {
                $this->handleFailedProcess($process);

                return;
            }

            $this->handleSuccessfulProcess($process);
        } catch (Exception $exception) {
            $this->check->failed('Exception occurred: '.$exception->getMessage());
        }
    }

    abstract public function handleSuccessfulProcess(Process $process);

    public function handleFailedProcess(Process $process)
    {
        $this->check->failed("Failed to run check {$this->check->type}: {$process->getErrorOutput()}");
    }

    abstract public function performNextRunInMinutes(): int;
}
