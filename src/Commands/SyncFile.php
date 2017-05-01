<?php

namespace Spatie\ServerMonitor\Commands;

use Spatie\ServerMonitor\Models\Host;
use Spatie\ServerMonitor\Models\Check;

class SyncFile extends BaseCommand
{
    protected $signature = 'server-monitor:sync-file
                            {path : Path to JSON file with hosts}
                            {--delete-missing : Delete hosts from the database which are not in the hosts file}';

    protected $description = 'One way sync hosts from JSON file to database';

    public function handle()
    {
        $json = file_get_contents($this->argument('path'));

        $hostsInFile = collect(json_decode($json, true));

        $this->createOrUpdateHostsFromFile($hostsInFile);

        $this->deleteMissingHosts($hostsInFile);
    }

    protected function createOrUpdateHostsFromFile($hostsInFile)
    {
        $hostsInFile->each(function ($hostAttributes) {
            $host = $this->createOrUpdateHost($hostAttributes);

            $this->syncChecks($host, $hostAttributes['checks']);
        });

        $this->info("Synced {$hostsInFile->count()} host(s) to database");
    }

    protected function deleteMissingHosts($hostsInFile)
    {
        if (! $this->option('delete-missing')) {
            return;
        }

        $this->determineHostModelClass()::all()
            ->reject(function (Host $host) use ($hostsInFile) {
                return $hostsInFile->contains('name', $host->name);
            })
            ->each(function (Host $host) {
                $this->comment("Deleted host `{$host->name}` from database because was not found in hosts file");
                $host->delete();
            });
    }

    protected function createOrUpdateHost(array $hostAttributes): Host
    {
        unset($hostAttributes['checks']);

        return tap($this->determineHostModelClass()::firstOrNew([
            'name' => $hostAttributes['name'],
        ]), function (Host $hostModel) use ($hostAttributes) {
            $hostModel
                ->fill($hostAttributes)
                ->save();
        });
    }

    protected function syncChecks(Host $host, array $checkTypes): Host
    {
        $this->removeChecksNotInArray($host, $checkTypes);

        $this->addChecksFromArray($host, $checkTypes);

        return $host;
    }

    protected function removeChecksNotInArray(Host $host, array $checkTypes)
    {
        $host->checks
            ->reject(function (Check $check) use ($checkTypes) {
                return in_array($check->type, $checkTypes);
            })
            ->each(function (Check $check) use ($host) {
                $this->comment("Deleted `{$check->type}` from host `{$host->name}` (not found in hosts file)");

                return $check->delete();
            });
    }

    protected function addChecksFromArray(Host $host, array $checkTypes)
    {
        collect($checkTypes)
            ->reject(function (string $checkType) use ($host) {
                return $host->hasCheckType($checkType);
            })
            ->each(function (string $checkType) use ($host) {
                $host->checks()->create(['type' => $checkType]);
            });
    }
}
