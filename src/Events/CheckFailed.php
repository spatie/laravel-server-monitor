<?php

namespace Spatie\ServerMonitor\Events;

use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\ServerMonitor\Models\Check;

class CheckFailed implements ShouldQueue
{
    /** @var \Spatie\ServerMonitor\Check */
    public $check;

    /** @var string */
    public $failureReason;

    public function __construct(Check $check, string $failureReason)
    {
        $this->check = $check;

        $this->failureReason = $failureReason;
    }
}
