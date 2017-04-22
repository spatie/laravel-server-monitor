<?php

namespace Spatie\ServerMonitor\Notifications;

use Illuminate\Config\Repository;
use Spatie\ServerMonitor\Events\Event;
use Illuminate\Contracts\Events\Dispatcher;
use Spatie\ServerMonitor\Events\CheckFailed;
use Spatie\ServerMonitor\Events\CheckWarning;
use Spatie\ServerMonitor\Events\CheckRestored;
use Spatie\ServerMonitor\Events\CheckSucceeded;

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

            if ($this->concernsSuccess($event)) {
                $notification->getCheck()->stopThrottlingFailedNotifications();
            }

            if ($notification->shouldSend()) {
                $notifiable = $this->determineNotifiable();

                $notifiable->setEvent($event);

                $notifiable->notify($notification);
            }

            if (! $this->concernsSuccess($event) && ! $notification->getCheck()->isThrottlingFailedNotifications()) {
                $notification->getCheck()->startThrottlingFailedNotifications();
            }
        });
    }

    protected function determineNotifiable()
    {
        $notifiableClass = $this->config->get('server-monitor.notifications.notifiable');

        return app($notifiableClass);
    }

    protected function determineNotification($event): ?BaseNotification
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

        return null;
    }

    protected function allEventClasses(): array
    {
        return [
            CheckSucceeded::class,
            CheckRestored::class,
            CheckWarning::class,
            CheckFailed::class,
        ];
    }

    protected function concernsSuccess(Event $event): bool
    {
        return in_array(get_class($event), [
            CheckSucceeded::class,
            CheckRestored::class,
        ]);
    }
}
