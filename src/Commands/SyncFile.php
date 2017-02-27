<?php

namespace Spatie\ServerMonitor\Commands;

use File;
use Illuminate\Database\Eloquent\Collection;
use Spatie\ServerMonitor\Models\Host;
use Spatie\ServerMonitor\Models\Check;

class SyncFile extends BaseCommand
{
    protected $signature = 'server-monitor:sync-file
                            {--force-export : Overwrite existing entries in hosts file with database entries}
                            {--delete-missing : Delete hosts from the database that are not in the hosts file}';

    protected $description = 'Syncs hosts in file with the database';

    protected $path;

    protected $filename = 'hosts.json';

    public function handle()
    {
        $this->prepareStorageDirectory();

        if($this->option('force-export') || ! File::exists($this->getFilepath())) {
            $this->exportAll();

            return;
        }

        $hostsInFile = collect(json_decode(File::get($this->getFilepath()), true));

        $this->updateOrCreateHosts($hostsInFile);

        $this->deleteMissingHosts($hostsInFile);

        $this->writeToFile(Host::all());
    }

    protected function getFilepath(): string
    {
        return $this->path.DIRECTORY_SEPARATOR.$this->filename;
    }

    protected function writeToFile(Collection $hosts)
    {
        $json = $hosts
            ->map(function (Host $host) {
                $checks = $host->checks->pluck('type');
                $host = collect($host)->only('name', 'ssh_user', 'ip', 'port')->toArray();
                $host['checks'] = $checks;

                return $host;
            })
            ->toJson();

        File::put($this->getFilepath(), $json);
    }

    protected function prepareStorageDirectory()
    {
        $this->path = storage_path('server-monitor');

        if (!File::exists($this->path)) {
            File::makeDirectory($this->path);
        }
    }

    protected function exportAll()
    {
        $this->writeToFile(Host::all());

        $this->info('Exported all hosts from database to ' . $this->getFilepath());

        return;
    }

    /**
     * @param $hostsInFile
     */
    protected function deleteMissingHosts($hostsInFile)
    {
        if ($this->option('delete-missing')) {
            Host::all()->each(function (Host $host) use ($hostsInFile) {
                if (!$hostsInFile->contains('name', $host->name)) {
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
                if (!in_array($check->type, $host['checks'])) {
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
