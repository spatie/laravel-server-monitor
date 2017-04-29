<?php

namespace Spatie\ServerMonitor\Commands;

use Illuminate\Console\Command;
use Spatie\ServerMonitor\HostRepository;
use Spatie\ServerMonitor\CheckRepository;
use Spatie\ServerMonitor\Helpers\ConsoleOutput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseCommand extends Command
{
    public function run(InputInterface $input, OutputInterface $output): int
    {
        app(ConsoleOutput::class)->setOutput($this);

        return parent::run($input, $output);
    }

    public function determineHostModelClass()
    {
        return HostRepository::determineHostModel();
    }

    public function determineCheckModelClass()
    {
        return CheckRepository::determineCheckModel();
    }
}
