<?php

use Spatie\ServerMonitor\Events\CheckSucceeded;
use Spatie\ServerMonitor\Models\Check;
use Spatie\ServerMonitor\Notifications\Notifiable;

beforeEach(function () {
    $this->createHost('my-host', null, ['diskspace']);

    $this->check = Check::first();
});

it('allows an event to be passed to the notifiable class', function () {
    $event = \Mockery::mock(CheckSucceeded::class);

    /** @var Notifiable $notifiable */
    $notifiable = app($this->app['config']->get('server-monitor.notifications.notifiable'));

    $notifiable->setEvent($event);

    expect($event)->toEqual($notifiable->getEvent());
});

it('can route notification for mail', function () {
    $this->app['config']->set('server-monitor.notifications.mail.to', 'test@test.com,other@other.com');
    $notifiable = new Notifiable();
    $mails = $notifiable->routeNotificationForMail();

    expect('test@test.com')->toEqual($mails[0]);
    expect('other@other.com')->toEqual($mails[1]);

    $this->app['config']->set('server-monitor.notifications.mail.to', ['test@test.com', 'other@other.com']);
    $mails = $notifiable->routeNotificationForMail();

    expect('test@test.com')->toEqual($mails[0]);
    expect('other@other.com')->toEqual($mails[1]);
});
