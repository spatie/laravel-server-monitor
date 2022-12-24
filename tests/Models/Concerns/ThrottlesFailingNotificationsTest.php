<?php

beforeEach(function () {
    $host = $this->createHost('localhost', 65000, ['diskspace']);

    $this->check = $host->checks->first();
});

it('can determine that it is not throttling', function () {
    expect($this->check->isThrottlingFailedNotifications())->toBeFalse();
});

it('can can start a throttling period', function () {
    $this->check->startThrottlingFailedNotifications();

    expect($this->check->isThrottlingFailedNotifications())->toBeTrue();
});

it('can can end a throttling period', function () {
    $this->check->startThrottlingFailedNotifications();

    $this->check->stopThrottlingFailedNotifications();

    expect($this->check->isThrottlingFailedNotifications())->toBeFalse();
});

it('the throttling period will end after an amount of minutes', function () {
    $this->check->startThrottlingFailedNotifications();

    $minutes = $this->check->getDefinition()->throttleFailingNotificationsForMinutes();

    expect($minutes)->toBeGreaterThan(0);

    expect($this->check->isThrottlingFailedNotifications())->toBeTrue();

    $this->progressMinutes($minutes - 1);

    expect($this->check->isThrottlingFailedNotifications())->toBeTrue();

    $this->progressMinutes(1);

    expect($this->check->isThrottlingFailedNotifications())->toBeFalse();
});
