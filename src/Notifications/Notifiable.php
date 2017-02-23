<?php

namespace Spatie\ServerMonitor\Notifications;

use Illuminate\Notifications\Notifiable as NotifiableTrait;

class Notifiable
{
    use NotifiableTrait;

    public function routeNotificationForMail(): ?string
    {
        return config('laravel-uptime-monitor.notifications.mail.to');
    }

    public function routeNotificationForSlack(): ?string
    {
        return config('server-monitor.notifications.slack.webhook_url');
    }

    public function getKey(): string
    {
        return static::class;
    }
}
