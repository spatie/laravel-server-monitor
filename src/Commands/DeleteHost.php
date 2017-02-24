<?php

namespace Spatie\ServerMonitor\Commands;

use Spatie\ServerMonitor\Models\Host;

class DeleteHost extends BaseCommand
{
    protected $signature = 'server-monitor:delete-host
                            {name : The host\'s name}';

    protected $description = 'Delete a host';

    public function handle()
    {
        $name = $this->argument('name');

        $host = Host::where('name', $name)->first();

        if (! $host) {
            return $this->error("Host with name '{$name}' not found.");
        }

        $host->delete();

        $this->info("{$name} deleted!");
    }
}
