<?php

namespace Spatie\ServerMonitor\Commands;

use Spatie\ServerMonitor\CheckRepository;

class RunChecks extends BaseCommand
{
    protected $signature = 'server-monitor:run-checks';

    protected $description = 'Run all checks';

    public function handle()
    {
        $checks = CheckRepository::allThatShouldRun();

        $this->info('Start running '.count($checks).' checks...');

        $checks->runAll();

        $this->info('All done!');
    }
}
