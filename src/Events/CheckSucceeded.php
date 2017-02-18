<?php

namespace Spatie\ServerMonitor\Events;

use Spatie\ServerMonitor\Models\Check;
use Illuminate\Contracts\Queue\ShouldQueue;

class CheckSucceeded implements ShouldQueue
{
    /** @var \Spatie\ServerMonitor\Check */
    public $check;

    /** @var string */
    protected $message;

    public function __construct(Check $check, string $message)
    {
        $this->check = $check;

        $this->message = $message;
    }
}
