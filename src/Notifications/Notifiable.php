<?php

namespace Spatie\ServerMonitor\Notifications;

use Illuminate\Notifications\Notifiable as NotifiableTrait;

class Notifiable
{
    use NotifiableTrait;

    /** @var \Spatie\ServerMonitor\Events\Event */
    protected $event;

    public function routeNotificationForMail(): ?array
    {
        $mails = config('server-monitor.notifications.mail.to');

        if (is_string($mails)) {
            $mails = explode(',', $mails);
        }

        return $mails;
    }

    public function routeNotificationForSlack(): ?string
    {
        return config('server-monitor.notifications.slack.webhook_url');
    }

    public function getKey(): string
    {
        return static::class;
    }

    /**
     * Get the event for the notification.
     *
     * @return \Spatie\ServerMonitor\Events\Event
     */
    public function getEvent(): \Spatie\ServerMonitor\Events\Event
    {
        return $this->event;
    }

    /**
     * Set the event for the notification.
     *
     * @param \Spatie\ServerMonitor\Events\Event $event
     *
     * @return Notifiable
     */
    public function setEvent(\Spatie\ServerMonitor\Events\Event $event): self
    {
        $this->event = $event;

        return $this;
    }
}
