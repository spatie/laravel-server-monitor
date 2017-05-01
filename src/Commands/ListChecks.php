<?php

namespace Spatie\ServerMonitor\Commands;

use Illuminate\Support\Collection;
use Spatie\ServerMonitor\Models\Check;

class ListChecks extends BaseCommand
{
    protected $signature = 'server-monitor:list-checks
                            {--host= : Only show checks for certain host}
                            {--check= : Only show certain check type}';

    protected $description = 'List all checks for host(s)';

    public function handle()
    {
        if ($this->determineHostModelClass()::count() === 0) {
            return $this->info('There are no hosts configured');
        }

        $this->unhealthyChecks();

        $this->healthyChecks();
    }

    protected function unhealthyChecks()
    {
        $this->tableWithTitle(
            'Unhealthy checks',
            ['Host', 'Check', 'Status', 'Message', 'Last checked', 'Next check'],
            $this->getTableRows($this->determineCheckModelClass()::unhealthy()->get())
        );
    }

    protected function healthyChecks()
    {
        $this->tableWithTitle(
            'Healthy checks',
            ['Host', 'Check', 'Message', 'Status', 'Last checked', 'Next check'],
            $this->getTableRows(self::determineCheckModelClass()::healthy()->get())
        );
    }

    protected function tableWithTitle(string $title, array $header, array $rows)
    {
        if (count($rows) === 0) {
            return;
        }

        $this->info($title);
        $this->info('================');
        $this->table($header, $rows);
        $this->comment('');
    }

    protected function getTableRows(Collection $checks): array
    {
        if ($hostName = $this->option('host')) {
            $checks = $checks->filter(function (Check $check) use ($hostName) {
                return $check->host->name === $hostName;
            });
        }

        if ($checkType = $this->option('check')) {
            $checks = $checks->filter(function (Check $check) use ($checkType) {
                return $check->type === $checkType;
            });
        }

        return $checks
            ->map(function (Check $check) {
                return [
                    'name' => $check->host->name,
                    'check' => $check->type,
                    'last_run_message' => $check->last_run_message,
                    'status' => $check->status_as_emoji,
                    'last_checked' => $check->getLatestRunDiffAttribute(),
                    'next_check' => $check->getNextRunDiffAttribute(),
                ];
            })
            ->toArray();
    }
}
