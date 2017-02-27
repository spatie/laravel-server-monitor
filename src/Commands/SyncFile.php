<?php

namespace Spatie\ServerMonitor\Commands;

use File;
use Spatie\ServerMonitor\Models\Host;
use Spatie\ServerMonitor\Models\Check;

class SyncFile extends BaseCommand
{
    protected $signature = 'server-monitor:sync-file
                            {filename : JSON file with hosts}
                            {--delete-missing : Delete hosts from the database that are not in the hosts file}';

    protected $description = 'One way sync hosts from JSON file to database';

    public function handle()
    {
        $json = File::get($this->argument('filename'));

        $hostsInFile = collect(json_decode($json, true));

        $this->updateOrCreateHosts($hostsInFile);

        $this->deleteMissingHosts($hostsInFile);
    }

    /**
     * @param $hostsInFile
     */
    protected function deleteMissingHosts($hostsInFile)
    {
        if ($this->option('delete-missing')) {
            Host::all()->each(function (Host $host) use ($hostsInFile) {
                if (! $hostsInFile->contains('name', $host->name)) {
                    $this->comment("Deleted host '{$host->name}' from database (was not found in hosts file)");
                    $host->delete();
                }
            });
        }
    }

    /**
     * @param $hostsInFile
     */
    protected function updateOrCreateHosts($hostsInFile)
    {
        $hostsInFile->each(function ($host) {
            $host = collect($host);

            $hostModel = Host::firstOrNew(['name' => $host['name']]);

            $hostModel
                ->fill($host->except('checks')->toArray())
                ->save();

            // Delete checks that were deleted from the file
            $hostModel->checks->each(function (Check $check) use ($host) {
                if (! in_array($check->type, $host['checks'])) {
                    $this->comment("Deleted '{$check->type}' from host '{$host['name']}' (not found in hosts file)");
                    $check->delete();
                }
            });

            // Add checks that do not exist in db
            foreach ($host['checks'] as $check) {
                if ($hostModel->checks->where('type', $check)->count() === 0) {
                    $hostModel->checks()->create(['type' => $check]);
                }
            }
        });

        $this->info("Synced {$hostsInFile->count()} host(s) to database");
    }
}
