<?php

namespace Spatie\ServerMonitor\Models\Enums;

class CheckStatus
{
    const NOT_YET_CHECKED = 'not yet checked';
    const OK = 'ok';
    const WARNING = 'warning';
    const FAILURE = 'failure';
}
