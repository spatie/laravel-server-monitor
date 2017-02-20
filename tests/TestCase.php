<?php

namespace Spatie\ServerMonitor\Test;

use Mockery;
use Carbon\Carbon;
use Spatie\ServerMonitor\Models\Host;
use Spatie\ServerMonitor\Models\Check;
use Symfony\Component\Process\Process;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;
use Spatie\ServerMonitor\ServerMonitorServiceProvider;

abstract class TestCase extends Orchestra
{
    /** @var Server */
    public $server;

    public function setUp()
    {
        Carbon::setTestNow(Carbon::create(2016, 1, 1, 00, 00, 00));

        parent::setUp();
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            ServerMonitorServiceProvider::class,
        ];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');

        $app['config']->set('mail.driver', 'log');

        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'prefix' => '',
            'database' => ':memory:',
        ]);

        $this->setUpDatabase();
    }

    public function tearDown()
    {
        Mockery::close();
    }

    protected function setUpDatabase()
    {
        include_once __DIR__.'/../database/migrations/create_hosts_table.php.stub';
        (new \CreateHostsTable())->up();

        include_once __DIR__.'/../database/migrations/create_checks_table.php.stub';
        (new \CreateChecksTable())->up();
    }

    public function progressMinutes(int $minutes)
    {
        $newNow = Carbon::now()->addMinutes($minutes);

        Carbon::setTestNow($newNow);
    }

    protected function createHost(string $hostName = 'localhost', int $port = 65000, $checks = null): Host
    {
        if (is_null($checks)) {
            $checks = ['diskspace'];
        }

        return tap(Host::create([
            'name' =>  $hostName,
            'port' => $port,
        ]), function(Host $host) use ($checks) {
            $host->checks()->saveMany(collect($checks)->map(function (string $checkName) {
                return new Check([
                    'type' => $checkName,
                    'status' => CheckStatus::class,
                ]);
            }));
        });
    }

    protected function getSuccessfulProcessWithOutput(string $output): Process
    {
        $process = new Process("echo {$output}");

        $process->start();

        while ($process->isRunning()) {
        }

        return $process;
    }

    protected function getFailedProcess(): Process
    {
        $process = new Process('blablabla');

        $process->start();

        while ($process->isRunning()) {
        }

        return $process;
    }

    protected function assertStringContains($needle, $haystack)
    {
        $this->assertTrue(str_contains($haystack, $needle), "String `{$haystack}` did not contain `{$needle}`");
    }
}
