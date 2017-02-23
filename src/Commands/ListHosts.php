<?php

namespace Spatie\ServerMonitor\Commands;

use Illuminate\Support\Collection;
use Spatie\ServerMonitor\Models\Host;
use Spatie\ServerMonitor\Models\Check;

class ListHosts extends BaseCommand
{
    protected $signature = 'server-monitor:list
                            {--host= : Filter hosts by name}
                            {--check= : Filter checks by type}';

    protected $description = 'List all hosts with their checks';

    public function handle()
    {
        $this->table(
            ['Host', 'Health', 'Checks'],
            $this->getTableRows(Host::all())
        );
    }

    protected function getTableRows(Collection $hosts): array
    {
        if ($hostName = $this->option('host')) {
            $hosts->filter(function (Host $host) use ($hostName) {
                return $host->name === $hostName;
            });
        }

        return Host::all()
            ->map(function (Host $host) {
                return [
                    'name' => $host->name,
                    'health' => $host->health_as_emoji,
                    'checks' => $this->getChecksSummary($host, $this->option('check')),
                ];
            })
            ->toArray();
    }

    protected function getChecksSummary(Host $host, ?string $typeFilter): string
    {
        return $host->checks
            ->filter(function (Check $check) use ($typeFilter) {
                if (is_null($typeFilter)) {
                    return true;
                }

                return $check->type === $typeFilter;
            })
            ->map(function (Check $check) {
                return $check->summary;
            })
            ->implode(PHP_EOL);
    }
}
