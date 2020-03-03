<?php

namespace Spatie\ServerMonitor;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Spatie\ServerMonitor\Exceptions\InvalidConfiguration;
use Spatie\ServerMonitor\Models\Host;

class HostRepository
{
    public static function all(): Collection
    {
        $hosts = self::query()->get();

        return $hosts;
    }

    protected static function query(): Builder
    {
        $modelClass = static::determineHostModel();

        return $modelClass::query();
    }

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
