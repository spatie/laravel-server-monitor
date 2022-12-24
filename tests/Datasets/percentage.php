<?php

use Spatie\ServerMonitor\Models\Enums\CheckStatus;

dataset('percentage', [
    [40, CheckStatus::SUCCESS],
    [50, CheckStatus::SUCCESS],
    [79, CheckStatus::SUCCESS],
    [80, CheckStatus::WARNING],
    [89, CheckStatus::WARNING],
    [90, CheckStatus::FAILED],
    [95, CheckStatus::FAILED],
]);
