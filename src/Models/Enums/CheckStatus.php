<?php

namespace Spatie\ServerMonitor\Models\Enums;

class CheckStatus
{
    const NOT_YET_CHECKED = 'not yet checked';
    const SUCCESS = 'success';
    const WARNING = 'warning';
    const FAILED = 'failed';
}
