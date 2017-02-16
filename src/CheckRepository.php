<?php

namespace Spatie\ServerMonitor;

use Spatie\ServerMonitor\Models\Check;

class CheckRepository
{
    public static function allThatShouldRun(): CheckCollection
    {
        $checks = self::query()->get()->filter->shouldRun();

        return new CheckCollection($checks);
    }

    protected static function query()
    {
        $modelClass = static::determineCheckModel();

        return $modelClass::enabled();
    }

    protected static function determineCheckModel(): string
    {
        $monitorModel = config('laravel-server-monitor.check_model') ?? Check::class;

        if (! is_a($monitorModel, Check::class, true)) {
            throw InvalidConfiguration::modelIsNotValid($monitorModel);
        }

        return $monitorModel;
    }
}
