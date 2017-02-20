<?php

namespace Spatie\UptimeMonitor\Test\Integration\Notifications;

use Notification;
use Carbon\Carbon;
use Spatie\ServerMonitor\Events\CheckFailed;
use Spatie\ServerMonitor\Notifications\Notifications\CheckFailed as CheckFailedNotification;
use Spatie\ServerMonitor\Test\TestCase;

class EventHandlerTest extends TestCase
{
    /** @var \Spatie\UptimeMonitor\Models\Monitor */
    protected $monitor;

    public function setUp()
    {
        parent::setUp();

        Notification::fake();
    }

    /**
     * @test
     *
     * @dataProvider eventClassDataProvider
     */
    public function it_can_send_a_notifications_for_certain_events(
        $eventClass,
        $notificationClass,
        $monitorAttributes,
        $shouldSendNotification
    ) {

        $host = $this->createHost();

        if (in_array($eventClass, [
            UptimeCheckFailedEvent::class,
            UptimeCheckRecoveredEvent::class,
        ])) {
            event(new $eventClass($monitor, new Period(Carbon::now(), Carbon::now())));
        } else {
            event(new $eventClass($monitor));
        }

        if ($shouldSendNotification) {
            Notification::assertSentTo(
                new Notifiable(),
                $notificationClass,
                function ($notification) use ($monitor) {
                    return $notification->event->monitor->id == $monitor->id;
                }
            );
        }

        if (! $shouldSendNotification) {
            Notification::assertNotSentTo(
                new Notifiable(),
                $notificationClass
            );
        }
    }

    public function eventClassDataProvider(): array
    {
        return [
            [CheckFailed::class, CheckFailedNotification::class, ['status' => \Spatie\ServerMonitor\Models\Enums\CheckStatus::FAILED], true],
        ];
    }



    /**
     * @test
     *
     * @dataProvider channelDataProvider
     */
    public function it_send_notifications_to_the_channels_configured_in_the_config_file(array $configuredChannels)
    {
        $this->app['config']->set(
            'laravel-uptime-monitor.notifications.notifications.'.UptimeCheckSucceeded::class,
            $configuredChannels
        );

        $monitor = factory(Monitor::class)->create();

        event(new UptimeCheckSucceededEvent($monitor));

        Notification::assertSentTo(
            new Notifiable(),
            UptimeCheckSucceeded::class,
            function ($notification, $usedChannels) use ($configuredChannels) {
                return $usedChannels == $configuredChannels;
            }
        );
    }

    public function channelDataProvider(): array
    {
        return [
            [['mail']],
            [['mail', 'slack']],
        ];
    }
}
