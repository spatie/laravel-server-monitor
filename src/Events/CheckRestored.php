<?php

namespace Spatie\ServerMonitor\Events;

use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\ServerMonitor\Models\Check;

class CheckRestored implements ShouldQueue
{
    /** @var \Spatie\ServerMonitor\Check */
    public $message;

    public function __construct(Check $check, string $message)
    {
        $this->check = $check;

        $this->message = $message;
    }
}
