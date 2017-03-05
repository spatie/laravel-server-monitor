<?php

namespace Spatie\ServerMonitor\Test\Integration\Notifications;

use Notification;
use Spatie\ServerMonitor\Test\TestCase;
use Spatie\ServerMonitor\Events\CheckFailed;
use Spatie\ServerMonitor\Events\CheckWarning;
use Spatie\ServerMonitor\Events\CheckRestored;
use Spatie\ServerMonitor\Events\CheckSucceeded;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;
use Spatie\ServerMonitor\Notifications\Notifiable;
use Spatie\ServerMonitor\Notifications\Notifications\CheckFailed as CheckFailedNotification;
use Spatie\ServerMonitor\Notifications\Notifications\CheckWarning as CheckWarningNotification;
use Spatie\ServerMonitor\Notifications\Notifications\CheckRestored as CheckRestoredNotification;
use Spatie\ServerMonitor\Notifications\Notifications\CheckSucceeded as CheckSucceededNotification;

class EventHandlerTest extends TestCase
{
    /** @var \Spatie\ServerMonitor\Models\Host */
    protected $host;

    /** @var \Spatie\ServerMonitor\Models\Check */
    protected $check;

    public function setUp()
    {
        parent::setUp();

        Notification::fake();

        $this->host = $this->createHost();

        $this->check = $this->host->checks->first();
    }

    /**
     * @test
     *
     * @dataProvider eventClassDataProvider
     */
    public function it_can_send_a_notifications_for_certain_events(
        $eventClass,
        $notificationClass,
        $checkStatus
    ) {
        $this->app['config']->set(
            'server-monitor.notifications.notifications.'.CheckSucceededNotification::class,
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
    }

    public function eventClassDataProvider(): array
    {
        return [
            [CheckFailed::class, CheckFailedNotification::class, CheckStatus::FAILED],
            [CheckSucceeded::class, CheckSucceededNotification::class, CheckStatus::SUCCESS],
            [CheckWarning::class, CheckWarningNotification::class, CheckStatus::WARNING],
            [CheckRestored::class, CheckRestoredNotification::class, CheckStatus::SUCCESS],
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
    }

    public function channelDataProvider(): array
    {
        return [
            [['mail']],
            [['mail', 'slack']],
        ];
    }
}
