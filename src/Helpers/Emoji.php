<?php

namespace Spatie\ServerMonitor\Helpers;

class Emoji
{
    public static function ok(): string
    {
        return "\u{2705}";
    }

    public static function notOk(): string
    {
        return "\u{274C}";
    }

    public static function rightwardsArrow(): string
    {
        return "\u{27A1}";
    }

    public static function unknown(): string
    {
        return "\u{2753}";
    }

    public static function warning(): string
    {
        return "\u{26A0}";
    }
}
