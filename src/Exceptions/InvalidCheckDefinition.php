<?php

namespace Spatie\ServerMonitor\Exceptions;

use Exception;
use Spatie\ServerMonitor\Models\Check;

class InvalidCheckDefinition extends Exception
{
    public static function unknownCheckType(Check $check)
    {
        $validValues = implode(', ', array_keys(config('server-monitor.checks')));

        return new static("The check with id `{$check->id}` has an unknown type `{$check->type}`. Valid values are {$validValues}");
    }

    public static function definitionClassDoesNotExist(Check $check, string $definitionClass)
    {
        return new static("The definition class {$definitionClass} specified in the configfile as `{$check->type}` does not exist");
    }
}
