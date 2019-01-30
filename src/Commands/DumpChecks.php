<?php

namespace Spatie\ServerMonitor\Commands;

use Spatie\ServerMonitor\Models\Host;
use Spatie\ServerMonitor\Models\Check;
use Spatie\ServerMonitor\HostRepository;

class DumpChecks extends BaseCommand
{
    protected $signature = 'server-monitor:dump-checks
                            {path : Path to JSON file}';

    protected $description = 'Dump current configuration to JSON file';

    public function handle()
    {
        $file = $this->argument('path');
        $jsonEncodedHosts = json_encode($this->getHostsWithChecks(), JSON_PRETTY_PRINT) . PHP_EOL;
        file_put_contents($file, $jsonEncodedHosts);
    }

    protected function getHostsWithChecks()
    {
        $hosts = HostRepository::all();
        $result = $hosts->map(
            function (Host $host) {
                $checks = $host->checks->map(
                    function (Check $check) {
                        return $check->type;
                    }
                )->toArray();

                $result = [
                    'name' => $host->name,
                    'ssh_user' => $host->ssh_user,
                    'checks' => $checks,
                ];
                if ($host->port) {
                    $result['port'] = $host->port;
                }
                if ($host->ip) {
                    $result['ip'] = $host->ip;
                }
                return $result;
            }
        )->toArray();

        return $result;
    }
}
