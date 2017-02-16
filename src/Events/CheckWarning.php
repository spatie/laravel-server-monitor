<?php

namespace Spatie\ServerMonitor\Events;

use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\ServerMonitor\Models\Check;

class CheckWarning implements ShouldQueue
{
    /** @var \Spatie\ServerMonitor\Check */
    public $check;

    /** @var string */
    public $warning;

    public function __construct(Check $check, string $warning)
    {
        $this->check = $check;

        $this->warning = $warning;
    }
}
