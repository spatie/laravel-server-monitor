<?php

namespace Spatie\ServerMonitor\Test;

use Artisan;
use Carbon\Carbon;
use Spatie\ServerMonitor\Models\Host;
use Spatie\ServerMonitor\Models\Check;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Notification;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;
use Spatie\ServerMonitor\ServerMonitorServiceProvider;

abstract class TestCase extends Orchestra
{
    /** @var Server */
    public $server;

    /** @var ?string */
    protected $consoleOutputCache;

    public function setUp()
    {
        Carbon::setTestNow(Carbon::create(2016, 1, 1, 00, 00, 00));

        $this->consoleOutputCache = null;

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

    protected function createHost(string $hostName = 'localhost', ?int $port = 65000, $checks = null): Host
    {
        if (is_null($checks)) {
            $checks = ['diskspace'];
        }

        $host = Host::create([
            'name' => $hostName,
            'port' => $port,
        ]);

        $host->checks()->saveMany(collect($checks)->map(function (string $checkName) {
            return new Check([
                'type' => $checkName,
                'status' => CheckStatus::NOT_YET_CHECKED,
            ]);
        }));

        return $host;
    }

    protected function getSuccessfulProcessWithOutput(string $output = 'my output'): Process
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

    protected function letSshServerRespondWithDiskspaceUsagePercentage(int $diskspaceUsagePercentage)
    {
        $listenFor = "bash -se <<EOF-LARAVEL-SERVER-MONITOR\nset -e\ndf -P .\nEOF-LARAVEL-SERVER-MONITOR";

        $respondWith = "Filesystem 512-blocks      Used Available Capacity  Mounted on\n/dev/disk1  974700800 830137776 144051024    {$diskspaceUsagePercentage}%    /\n";

        SshServer::setResponse($listenFor, $respondWith);
    }

    /**
     * @param string|array $searchStrings
     */
    protected function seeInConsoleOutput($searchStrings)
    {
        if (! is_array($searchStrings)) {
            $searchStrings = [$searchStrings];
        }
        $output = $this->getArtisanOutput();
        foreach ($searchStrings as $searchString) {
            $this->assertContains((string) $searchString, $output);
        }
    }

    /**
     * @param string|array $searchStrings
     */
    protected function dontSeeInConsoleOutput($searchStrings)
    {
        if (! is_array($searchStrings)) {
            $searchStrings = [$searchStrings];
        }
        $output = $this->getArtisanOutput();
        foreach ($searchStrings as $searchString) {
            $this->assertNotContains((string) $searchString, $output);
        }
    }

    protected function getArtisanOutput(): string
    {
        $this->consoleOutputCache .= Artisan::output();

        return $this->consoleOutputCache;
    }

    protected function resetNotificationAssertions()
    {
        Notification::fake();
    }

    protected function skipIfDummySshServerIsNotRunning()
    {
        if ((new Process('ssh localhost -p 65000 "echo"'))->run() === 255) {
            $this->markTestSkipped('Dummy SSH server is not running.');
        }
    }
}
