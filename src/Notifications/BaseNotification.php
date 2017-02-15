<?php

namespace Spatie\ServerMonitor\Notifications;

use Illuminate\Notifications\Notification;
use Spatie\ServerMonitor\Check;

abstract class BaseNotification extends Notification
{
    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return config('server-monitor.notifications.notifications.'.static::class);
    }

    public function getCheck(): Check
    {
        return $this->event->check;
    }
}
