<?php

use Illuminate\Support\Facades\Notification;
use Spatie\ServerMonitor\Events\CheckSucceeded;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;
use Spatie\ServerMonitor\Notifications\Notifiable;
use Spatie\ServerMonitor\Notifications\Notifications\CheckSucceeded as CheckSucceededNotification;

beforeEach(function () {
    Notification::fake();

    $this->host = $this->createHost();

    $this->check = $this->host->checks->first();
});

it('can send a notifications for certain events', function ($eventClass, $notificationClass, $checkStatus) {
    $this->app['config']->set(
        'server-monitor.notifications.notifications.'.$notificationClass,
        ['slack']
    );

    $this->check->status = $checkStatus;
    $this->check->save();

    event(new $eventClass($this->check, ''));

    Notification::assertSentTo(
        new Notifiable(),
        $notificationClass,
        function ($notification) {
            return $notification->event->check->id == $this->check->id;
        }
    );
})->with('event_class');

it('send notifications to the channels configured in the config file', function (array $configuredChannels) {
    $this->app['config']->set(
        'server-monitor.notifications.notifications.'.CheckSucceededNotification::class,
        $configuredChannels
    );

    $this->check->status = CheckStatus::SUCCESS;
    $this->check->save();

    event(new CheckSucceeded($this->check, ''));

    Notification::assertSentTo(
        new Notifiable(),
        CheckSucceededNotification::class,
        function ($notification, $usedChannels) use ($configuredChannels) {
            return $usedChannels == $configuredChannels;
        }
    );
})->with('channel');
