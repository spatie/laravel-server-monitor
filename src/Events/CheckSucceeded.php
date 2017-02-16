<?php

namespace Spatie\ServerMonitor\Events;

use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\ServerMonitor\Models\Check;

class CheckSucceeded implements ShouldQueue
{
    /** @var \Spatie\ServerMonitor\Check */
    public $check;

    public function __construct(Check $check)
    {
        $this->check = $check;
    }
}
