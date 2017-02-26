<?php

namespace Spatie\ServerMonitor\Commands;

use Spatie\ServerMonitor\Models\Host;

class DeleteHost extends BaseCommand
{
    protected $signature = 'server-monitor:delete-host
                            {name : The name of the host to be deleted}';

    protected $description = 'Delete a host';

    public function handle()
    {
        $name = $this->argument('name');

        $host = Host::where('name', $name)->first();

        if (! $host) {
            return $this->error("Host with name '{$name}' not found.");
        }

        $host->delete();

        $this->info("Host {$name}  was deleted!");
    }
}
