<?php

namespace Spatie\ServerMonitor\Models\Concerns;

use Carbon\Carbon;

trait ThrottlesFailingNotifications
{
    public function isThrottlingFailedNotifications(): bool
    {
        if (is_null($this->started_throttling_failing_notifications_at)) {
            return false;
        }

        $throttleDuration = $this->getDefinition()->throttleFailingNotificationsForMinutes();

        $throttlePeriodEnd = $this->started_throttling_failing_notifications_at->copy()->addMinutes($throttleDuration);

        return $throttlePeriodEnd->isFuture();
    }

    public function stopThrottlingFailedNotifications()
    {
        $this->started_throttling_failing_notifications_at = null;

        $this->save();
    }

    public function startThrottlingFailedNotifications()
    {
        $this->started_throttling_failing_notifications_at = Carbon::now();

        $this->save();
    }
}
