<?php

namespace Spatie\ServerMonitor\Exceptions;

use Exception;
use Spatie\ServerMonitor\Models\Check;

class InvalidConfiguration extends Exception
{
    public static function modelIsNotValid(string $className)
    {
        return new static("The given model class `{$className}` does not extend `".Check::class.'`');
    }
}
