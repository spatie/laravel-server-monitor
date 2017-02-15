<?php

namespace Spatie\ServerMonitor\CheckDefinitions;

class CheckDefinition
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

    public function getCommand(): string
    {

    }
}