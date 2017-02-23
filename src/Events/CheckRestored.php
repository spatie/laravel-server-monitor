<?php

namespace Spatie\ServerMonitor\Events;

use Spatie\ServerMonitor\Models\Check;
use Illuminate\Contracts\Queue\ShouldQueue;

class CheckRestored implements ShouldQueue
{
    public function __construct(Check $check)
    {
        $this->check = $check;
    }
}
