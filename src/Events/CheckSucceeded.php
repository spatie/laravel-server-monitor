<?php

namespace Spatie\CheckSucceeded\Events;

use Spatie\ServerMonitor\Check;
use Illuminate\Contracts\Queue\ShouldQueue;

class CheckSucceeded implements ShouldQueue
{
    /** @var \Spatie\ServerMonitor\Check */
    public $check;

    public function __construct(Check $check)
    {
        $this->check = $check;
    }
}
