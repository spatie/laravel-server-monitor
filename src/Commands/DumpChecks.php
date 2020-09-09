<?php

namespace Spatie\ServerMonitor\Commands;

use Spatie\ServerMonitor\HostRepository;
use Spatie\ServerMonitor\Models\Check;
use Spatie\ServerMonitor\Models\Host;

class DumpChecks extends BaseCommand
{
    protected $signature = 'server-monitor:dump-checks
                            {path : Path to json file}';

    protected $description = 'Dump current configuration to json file';

    public function handle()
    {
        $jsonEncodedHosts = json_encode($this->getHostsWithChecks(), JSON_PRETTY_PRINT).PHP_EOL;

        file_put_contents($this->argument('path'), $jsonEncodedHosts);
    }

    protected function getHostsWithChecks(): array
    {
        return HostRepository::all()
            ->map(function (Host $host) {
                return array_filter([
                    'name' => $host->name,
                    'ssh_user' => $host->ssh_user,
                    'port' => $host->port,
                    'ip' => $host->ip,
                    'checks' => $host->checks
                        ->map(
                            function (Check $check) {
                                return $check->type;
                            }
                        )
                        ->toArray(),
                ]);
            })
            ->toArray();
    }
}
