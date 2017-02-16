<?php

namespace Spatie\ServerMonitor;

use Countable;
use Illuminate\Support\Collection;
use Spatie\ServerMonitor\Models\Check;

class CheckCollection implements Countable
{
    /** @var \Illuminate\Support\Collection */
    protected $checks;

    /** @var \Illuminate\Support\Collection */
    protected $runningChecks;

    public function __construct(Collection $checks)
    {
        $this->checks = $checks;

        $this->runningChecks = collect();
    }

    public function run()
    {
        while ($this->hasPendingChecks()) {
            if ($this->runningChecks->count() < config('server-monitor.concurrent_ssh_connections')) {
                $this->startNextCheck();
            }

            $this->cleanRunningChecks();
        }
    }

    protected function hasPendingChecks(): bool
    {
        return $this->checks->count() > 0;
    }

    protected function startNextCheck()
    {
        $check = $this->checks->shift();

        $check->getProcess()->start();

        $this->runningChecks->push($check);
    }

    protected function cleanRunningChecks()
    {
        [$this->runningChecks, $finishedChecks] = $this->runningChecks->partition(function (Check $check) {
            return $check->getProcess()->isRunning();
        });

        $this->processFinishedChecks($finishedChecks);
    }

    protected function processFinishedChecks(Collection $finishedChecks)
    {
        $finishedChecks->each(function(Check $check) {
            $check->getDefinition()->handleFinishedProcess($check->getProcess());
        });
    }

    public function count()
    {
        return count($this->checks);
    }
}