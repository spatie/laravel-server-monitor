<?php

namespace Spatie\ServerMonitor\Test;

use Event;
use Artisan;
use Carbon\Carbon;
use Mockery;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\ServerMonitor\ServerMonitorServiceProvider;

abstract class TestCase extends Orchestra
{
    /** @var  Server */
    public $server;

    public function setUp()
    {
        Carbon::setTestNow(Carbon::create(2016, 1, 1, 00, 00, 00));

        parent::setUp();

        $this->server = new Server();
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
}
