<?php

namespace Spatie\ServerMonitor\CheckDefinitions;

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

    abstract public function performNextRunInMinutes(): int;

    abstract public function handleFinishedProcess(Process $process);
}