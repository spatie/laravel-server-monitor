<?php

namespace Spatie\ServerMonitor;

use Illuminate\Support\ServiceProvider;

class ServerMonitorServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/server-monitor.php' => config_path('server-monitor.php'),
            ], 'config');

            $this->publishesMigration('CreateHostsTable', 'create_hosts_table');
            $this->publishesMigration('CreateChecksTable', 'create_checks_table');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/server-monitor.php', 'ServerMonitor');
    }

    protected function publishesMigration(string $className, string $fileName)
    {
        if (! class_exists($className)) {
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__ . "/../database/migrations/{$fileName}.php.stub" => database_path('migrations/' . $timestamp . "_{$fileName}.php"),
            ], 'migrations');
        }
    }
}
