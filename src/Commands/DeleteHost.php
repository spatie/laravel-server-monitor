<?php

namespace Spatie\ServerMonitor\Commands;

class DeleteHost extends BaseCommand
{
    protected $signature = 'server-monitor:delete-host
                            {name : The name of the host to be deleted}';

    protected $description = 'Delete a host';

    public function handle()
    {
        $name = $this->argument('name');

        $host = $this->determineHostModelClass()::where('name', $name)->first();

        if (! $host) {
            return $this->error("Host with name `{$name}` not found.");
        }

        if (! $this->confirm("Are you sure you wish to delete `{$name}`?")) {
            return;
        }

        $host->delete();

        $this->info("Host `{$name}` was deleted!");
    }
}
