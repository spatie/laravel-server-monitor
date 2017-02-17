<?php

namespace Spatie\ServerMonitor\Commands;

use InvalidArgumentException;
use Spatie\ServerMonitor\Models\Host;

class AddHost extends BaseCommand
{
    protected $signature = 'monitor:add-host';

    protected $description = 'Add a host';

    protected $allChecksLabel  ='<every check>';

    public function handle()
    {
        $this->info("Let's add a host!");

        $hostName = $this->ask("What's the name of the host");

        $sshUser = $this->confirm("Should a custom ssh user be used?")
            ? $this->ask('Which ssh?')
            : null;

        $port = $this->confirm("Should a custom port be used?")
            ? $this->ask('Which port?')
            : null;

        $checkNames = array_merge([$this->allChecksLabel], $this->getAllCheckNames());

        $chosenChecks = $this->choice("Which checks should be performed?", $checkNames,0, null, true);

        $chosenChecks = $this->determineChecks($chosenChecks, $checkNames);

        if (Host::where('name', $hostName)->first()) {
            throw new InvalidArgumentException("Host `{$hostName}` already exitst");
        };

        Host::create([
            'name' => $hostName,
            'ssh_user' => $sshUser,
            'port' => $port,
        ])->checks()->$chosenChecks;

        $this->info("Host `{$hostName}` added");
    }

    protected function determineChecks(array $chosenChecks, array $checkNames): array
    {
        if (in_array($this->allChecksLabel, $chosenChecks)) {
            return $checkNames;
        }

        return array_diff($chosenChecks, [$this->allChecksLabel]);
    }

    protected function getAllCheckNames(): array
    {
        return array_keys(config('server-monitor.checks'));
    }

}
