<?php

namespace Spatie\ServerMonitor\Notifications;

use Spatie\ServerMonitor\Models\Check;
use Illuminate\Notifications\Notification;

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

    protected function getMessageText(): ?string
    {
        return ucfirst($this->getCheck()->last_run_message);
    }

    protected function getSubject(): string
    {
        return "{$this->getCheck()->type} on {$this->getCheck()->host->name}";
    }

    abstract public function shouldSend(): bool;
}
