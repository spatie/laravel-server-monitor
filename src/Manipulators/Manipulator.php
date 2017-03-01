<?php

namespace Spatie\ServerMonitor\Manipulators;

use Spatie\ServerMonitor\Models\Check;
use Symfony\Component\Process\Process;

interface Manipulator
{
    public function manipulateProcess(Process $process, Check $check): Process;
}
