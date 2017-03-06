<?php

namespace Spatie\ServerMonitor\CheckDefinitions;

use Exception;
use Spatie\ServerMonitor\Models\Check;
use Symfony\Component\Process\Process;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;

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

    public function command(): string
    {
        return $this->command;
    }

    public function determineResult(Process $process)
    {
        $this->check->storeProcessOutput($process);

        try {
            if (! empty($process->getErrorOutput())) {
                $this->resolveFailed($process);

                return;
            }

            $this->resolve($process);
        } catch (Exception $exception) {
            $this->check->fail('Exception occurred: '.$exception->getMessage());
        }
    }

    abstract public function resolve(Process $process);

    public function resolveFailed(Process $process)
    {
        $this->check->fail("failed to run: {$process->getErrorOutput()}");
    }

    /**
     * When a check is emitting a warning or is failing, a notification will only
     * be sent once in given amount of minutes.
     *
     * @return int
     */
    public function throttleFailingNotificationsForMinutes(): int
    {
        return config('server-monitor.notifications.throttle_failing_notifications_for_minutes');
    }

    public function performNextRunInMinutes(): int
    {
        if ($this->check->hasStatus(CheckStatus::SUCCESS)) {
            return 10;
        }

        return 0;
    }

    /**
     * The amount of seconds that check may run.
     *
     * @return int
     */
    public function timeoutInSeconds(): int
    {
        return 10;
    }
}
