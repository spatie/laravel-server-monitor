<?php

namespace Spatie\ServerMonitor\Notifications;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Spatie\CheckSucceeded\Events\CheckFailed;
use Spatie\CheckSucceeded\Events\CheckSucceeded;
use Spatie\CheckSucceeded\Events\CheckWarning;
use Spatie\ServerMonitor\Events\UptimeCheckFailed;
use Spatie\ServerMonitor\Events\UptimeCheckRecovered;
use Spatie\ServerMonitor\Events\UptimeCheckSucceeded;
use Spatie\ServerMonitor\Events\CertificateCheckFailed;
use Spatie\ServerMonitor\Events\CertificateExpiresSoon;
use Spatie\ServerMonitor\Events\CertificateCheckSucceeded;

class EventHandler
{
    /** @var \Illuminate\Config\Repository */
    protected $config;

    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    public function subscribe(Dispatcher $events)
    {
        $events->listen($this->allEventClasses(), function ($event) {
            $notification = $this->determineNotification($event);

            if (! $notification) {
                return;
            }

            if ($notification->isStillRelevant()) {
                $notifiable = $this->determineNotifiable();

                $notifiable->notify($notification);
            }
        });
    }

    protected function determineNotifiable()
    {
        $notifiableClass = $this->config->get('server-monitor.notifications.notifiable');

        return app($notifiableClass);
    }

    protected function determineNotification($event)
    {
        $eventName = class_basename($event);

        $notificationClass = collect($this->config->get('server-monitor.notifications.notifications'))
            ->filter(function (array $notificationChannels) {
                return count($notificationChannels);
            })
            ->keys()
            ->first(function ($notificationClass) use ($eventName) {
                $notificationName = class_basename($notificationClass);

                return $notificationName === $eventName;
            });

        if ($notificationClass) {
            return app($notificationClass)->setEvent($event);
        }
    }

    protected function allEventClasses(): array
    {
        return [
            CheckSucceeded::class,
            CheckWarning::class,
            CheckFailed::class,
        ];
    }
}
