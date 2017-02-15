<?php

namespace Spatie\ServerMonitor\CheckDefinitions;

use Carbon\Carbon;
use Symfony\Component\Process\Process;

abstract class CheckDefinition
{
    /** @var \Spatie\ServerMonitor\Models\Check */
    protected $check;

    /**
     * @param \Spatie\ServerMonitor\CheckDefinitions\Check $check
     *
     * @return $this
     */
    public function setCheck(Check $check)
    {
        $this->check = $check;

        return $this;
    }

    abstract public function getCommand();

    abstract public function performNextRunAt(): Carbon;

    abstract public function handleFinishedProcess(Process $process);
}