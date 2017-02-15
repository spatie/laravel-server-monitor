<?php

namespace Spatie\ServerMonitor;

use Generator;

class CheckCollection extends Collection
{
    public function run()
    {
        while($process = $this->getNextProcess())
        {

        }
    }

    public function getNextProcess(): Generator
    {

    }
}