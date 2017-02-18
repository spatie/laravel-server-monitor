<?php

namespace Spatie\ServerMonitor\Events;

use Spatie\ServerMonitor\Models\Check;
use Illuminate\Contracts\Queue\ShouldQueue;

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
