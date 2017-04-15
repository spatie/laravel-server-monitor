<?php

namespace Spatie\ServerMonitor\Test\Integration\Notifications;

use Notification;
use Spatie\ServerMonitor\Models\Check;
use Spatie\ServerMonitor\Test\TestCase;
use Spatie\ServerMonitor\Events\CheckSucceeded;
use Spatie\ServerMonitor\Notifications\Notifiable;


class NotifiableTest extends TestCase
{
    /** @var \Spatie\ServerMonitor\Models\Check */
    protected $check;

    public function setUp()
    {
        parent::setUp();

        $this->createHost('my-host', null, ['diskspace']);

        $this->check = Check::first();
    }

    /** @test */
    public function it_allows_an_event_to_be_passed_to_the_notifiable_class()
    {
        $event = \Mockery::mock(CheckSucceeded::class);

        /** @var Notifiable $notifiable */
        $notifiable = app($this->app['config']->get('server-monitor.notifications.notifiable'));

        $notifiable->setEvent($event);

        $this->assertEquals($notifiable->getEvent(), $event);

    }

}
