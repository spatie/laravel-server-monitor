<?php

use Spatie\ServerMonitor\Events\CheckFailed;
use Spatie\ServerMonitor\Events\CheckRestored;
use Spatie\ServerMonitor\Events\CheckSucceeded;
use Spatie\ServerMonitor\Events\CheckWarning;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;
use Spatie\ServerMonitor\Notifications\Notifications\CheckFailed as CheckFailedNotification;
use Spatie\ServerMonitor\Notifications\Notifications\CheckRestored as CheckRestoredNotification;
use Spatie\ServerMonitor\Notifications\Notifications\CheckSucceeded as CheckSucceededNotification;
use Spatie\ServerMonitor\Notifications\Notifications\CheckWarning as CheckWarningNotification;

dataset('event_class', [
    [CheckFailed::class, CheckFailedNotification::class, CheckStatus::FAILED],
    [CheckSucceeded::class, CheckSucceededNotification::class, CheckStatus::SUCCESS],
    [CheckWarning::class, CheckWarningNotification::class, CheckStatus::WARNING],
    [CheckRestored::class, CheckRestoredNotification::class, CheckStatus::SUCCESS],
]);

dataset('channel', [
    [['mail']],
    [['mail', 'slack']],
]);
