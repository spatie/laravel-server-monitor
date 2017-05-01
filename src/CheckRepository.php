<?php

namespace Spatie\ServerMonitor;

use Spatie\ServerMonitor\Models\Check;
use Illuminate\Database\Eloquent\Builder;
use Spatie\ServerMonitor\Exceptions\InvalidConfiguration;

class CheckRepository
{
    public static function allThatShouldRun(): CheckCollection
    {
        $checks = self::query()->get()->filter->shouldRun();

        return new CheckCollection($checks);
    }

    protected static function query(): Builder
    {
        $modelClass = static::determineCheckModel();

        return $modelClass::enabled();
    }

    public static function determineCheckModel(): string
    {
        $monitorModel = config('server-monitor.check_model') ?? Check::class;

        if (! is_a($monitorModel, Check::class, true)) {
            throw InvalidConfiguration::checkModelIsNotValid($monitorModel);
        }

        return $monitorModel;
    }
}
