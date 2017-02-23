<?php

namespace Spatie\ServerMonitor\Commands;

use InvalidArgumentException;
use Spatie\ServerMonitor\Models\Host;
use Spatie\ServerMonitor\Models\Check;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;

class AddHost extends BaseCommand
{
    protected $signature = 'server-monitor:add-host';

    protected $description = 'Add a host';

    public static $allChecksLabel = '<every check>';

    public function handle()
    {
        $this->info("Let's add a host!");

        $hostName = $this->ask('What is the name of the host');

        $sshUser = $this->confirm('Should a custom ssh user be used?')
            ? $this->ask('Which user?')
            : null;

        $port = $this->confirm('Should a custom port be used?')
            ? $this->ask('Which port?')
            : null;

        $checkNames = array_merge([static::$allChecksLabel], $this->getAllCheckNames());

        $chosenChecks = $this->choice('Which checks should be performed?', $checkNames, 0, null, true);

        $chosenChecks = $this->determineChecks($chosenChecks, $checkNames);

        if (Host::where('name', $hostName)->first()) {
            throw new InvalidArgumentException("Host `{$hostName}` already exitst");
        }

        Host::create([
            'name' => $hostName,
            'ssh_user' => $sshUser,
            'port' => $port,
        ])->checks()->saveMany(collect($chosenChecks)->map(function (string $checkName) {
            return new Check([
                'type' => $checkName,
                'status' => CheckStatus::NOT_YET_CHECKED,
                'custom_properties' => [],
            ]);
        }));

        $this->info("Host `{$hostName}` added");
    }

    protected function determineChecks(array $chosenChecks, array $checkNames): array
    {
        if (in_array(static::$allChecksLabel, $chosenChecks)) {
            return $this->getAllCheckNames();
        }

        return array_diff($chosenChecks, [static::$allChecksLabel]);
    }

    protected function getAllCheckNames(): array
    {
        return array_keys(config('server-monitor.checks'));
    }
}
