<?php
/**
 * Created by PhpStorm.
 * User: dd
 * Date: 18.06.2018
 * Time: 02:52
 */

namespace Spatie\ServerMonitor\Excluded;


use Illuminate\Support\Facades\Config;
use phpDocumentor\Reflection\Types\Boolean;

class ExcludedErrors
{



    public static function getExcludedErrors() : array
    {

        return Config::get("server-monitor.excluded_errors",[]);

    }


    public static function hasExcludedError(String $errorString) : Bool
    {

        $excludedErrors = self::getExcludedErrors();



        foreach ($excludedErrors as $excludedError)
        {
            $counter=0;
            str_replace($excludedError,"_REPLACE_",$errorString,$counter);
            if($counter>0) return true;
        }

        return false;

    }


}