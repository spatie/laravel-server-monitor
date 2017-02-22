<?php

namespace Spatie\ServerMonitor\Commands;

use Spatie\ServerMonitor\Models\Host;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;

class ListHosts extends BaseCommand
{
    protected $signature = 'monitor:list
                            {--host= : Filter hosts by name}
                            {--check= : Filter checks by type}';

    protected $description = 'List all hosts with their checks';

    public function handle()
    {
        $hostName = $this->option('host');
        $checkType = $this->option('check');

        $hostsQuery = Host::query();

        if ($hostName) {
            $hostsQuery->where('name', 'LIKE', "%{$hostName}%");
        }

        if ($checkType) {
            $hostsQuery
                ->whereHas('checks', function ($query) use ($checkType) {
                    $query->where('type', 'LIKE', "%{$checkType}%");
                })
                ->with(['checks' => function ($query) use ($checkType) {
                    $query->where('type', 'LIKE', "%{$checkType}%");
                }]);
        }

        $hosts = $hostsQuery->get();

        $this->renderTable($hosts);
    }

    protected function renderTable($hosts)
    {
        $rows = $hosts->map(function ($host) {
            $name = $host->name;

            $checks = $this->formatCheckStatusCountForHost($host);

            $messages = $this->formatCheckMessagesForHost($host);

            return compact('name', 'checks', 'messages');
        });

        $header = ['Host', 'Checks', 'Message'];

        $this->table($header, $rows);
    }

    protected function formatCheckStatusCountForHost(Host $host): string
    {
        $statuses = collect([CheckStatus::SUCCESS, CheckStatus::FAILED, CheckStatus::NOT_YET_CHECKED, CheckStatus::WARNING]);

        $checks = $statuses
            ->map(function ($status) use ($host) {
                $checksWithStatus = $host->checks->where('status', $status);
                $statusCount = $checksWithStatus->count();
                $emoji = $checksWithStatus->first()->statusAsEmoji ?? '';

                return $statusCount ? "{$emoji} {$statusCount}  " : '';
            })
            ->implode('');

        return substr($checks, 0, -2);
    }

    protected function formatCheckMessagesForHost(Host $host): string
    {
        return $host->checks
            ->filter(function ($check) {
                return ! empty($check->message);
            })
            ->map(function ($check) {
                return "<fg=black;bg=cyan>{$check->type}</>: {$check->message}";
            })
            ->implode("\n");
    }
}
