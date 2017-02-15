<?php

namespace Spatie\UptimeMonitor\Commands;

use Spatie\ServerMonitor\CheckRepository;

class CheckUptime extends BaseCommand
{
    protected $signature = 'monitor:run-checks';

    protected $description = 'Run all checks';

    public function handle()
    {
        $checks = CheckRepository::allThatShouldRun();

        $this->comment('Start running '.count($checks).' checks...');

        $checks->run();

        $this->info('All done!');
    }
}
