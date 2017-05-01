<?php

namespace Spatie\ServerMonitor;

use Spatie\ServerMonitor\Models\Host;
use Spatie\ServerMonitor\Exceptions\InvalidConfiguration;

class HostRepository
{
    /**
     * Determine the host model class name.
     *
     * @return string
     *
     * @throws \Spatie\ServerMonitor\Exceptions\InvalidConfiguration
     */
    public static function determineHostModel(): string
    {
        $hostModel = config('server-monitor.host_model') ?? Host::class;

        if (! is_a($hostModel, Host::class, true)) {
            throw InvalidConfiguration::hostModelIsNotValid($hostModel);
        }

        return $hostModel;
    }
}
