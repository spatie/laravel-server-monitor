<?php

namespace Spatie\ServerMonitor;

use Countable;
use Illuminate\Support\Collection;
use Spatie\ServerMonitor\Models\Check;
use Spatie\ServerMonitor\Helpers\ConsoleOutput;

class CheckCollection implements Countable
{
    /** @var \Illuminate\Support\Collection */
    protected $pendingChecks;

    /** @var \Illuminate\Support\Collection */
    protected $runningChecks;

    public function __construct(Collection $checks)
    {
        $this->pendingChecks = $checks;

        $this->runningChecks = collect();
    }

    public function runAll()
    {
        while ($this->pendingChecks->isNotEmpty() || $this->runningChecks->isNotEmpty()) {
            if ($this->runningChecks->count() < config('server-monitor.concurrent_ssh_connections')) {
                $this->startNextCheck();
            }

            $this->handleFinishedChecks();
        }
    }

    protected function startNextCheck()
    {
        if ($this->pendingChecks->isEmpty()) {
            return;
        }

        $check = $this->pendingChecks->shift();

        ConsoleOutput::comment($check->host->name.": performing check `{$check->type}`...");

        $check->getProcess()->start();

        $this->runningChecks->push($check);
    }

    protected function handleFinishedChecks()
    {
        [$this->runningChecks, $finishedChecks] = $this->runningChecks->partition(function (Check $check) {
            return $check->getProcess()->isRunning();
        });

        $finishedChecks->each->handleFinishedProcess();
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->pendingChecks);
    }
}
