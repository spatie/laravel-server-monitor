<?php

use Spatie\ServerMonitor\Models\Check;
use Spatie\ServerMonitor\Models\Host;

beforeEach(function () {
    $this->createHost('my-host', null, ['diskspace']);

    $this->check = Check::first();
});

it('uses the host in the process command', function () {
    $this->check->getProcessCommand();

    expect($this->check->getProcessCommand())->toStartWith("ssh my-host 'bash");
});

it('uses its host custom port in the process command', function () {
    tap($this->check->host, function (Host $host) {
        $host->port = 123;
        $host->save();
    });

    $this->check->getProcessCommand();

    expect($this->check->getProcessCommand())->toStartWith("ssh my-host -p 123 'bash");
});

it('uses its host custom ssh user in the process command', function () {
    tap($this->check->host, function (Host $host) {
        $host->ssh_user = 'my-ssh-user';
        $host->save();
    });

    $this->check->getProcessCommand();

    expect($this->check->getProcessCommand())->toStartWith("ssh my-ssh-user@my-host 'bash");
});

it('will use the ip address instead of the host name', function () {
    tap($this->check->host, function (Host $host) {
        $host->ip = '1.2.3.4';
        $host->save();
    });

    $this->check->getProcessCommand();

    expect($this->check->getProcessCommand())->toStartWith("ssh 1.2.3.4 'bash");
});

it('will use the prefix specified in the config file', function () {
    $prefix = '-q';

    $this->app['config']->set('server-monitor.ssh_command_prefix', $prefix);

    tap($this->check->host, function (Host $host) {
        $host->ip = '1.2.3.4';
        $host->save();
    });

    $this->check->getProcessCommand();

    expect($this->check->getProcessCommand())->toStartWith("ssh {$prefix} 1.2.3.4 'bash");
});

it('will use the suffix specified in the config file', function () {
    $suffix = '-o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no';

    $this->app['config']->set('server-monitor.ssh_command_suffix', $suffix);

    tap($this->check->host, function (Host $host) {
        $host->ip = '1.2.3.4';
        $host->save();
    });

    $this->check->getProcessCommand();

    expect($this->check->getProcessCommand())->toStartWith("ssh 1.2.3.4 {$suffix} 'bash");
});
