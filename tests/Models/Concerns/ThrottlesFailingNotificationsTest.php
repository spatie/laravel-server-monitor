<?php

namespace Spatie\ServerMonitor\Test\Models\Concerns;

use Spatie\ServerMonitor\Test\TestCase;

class ThrottlesFailingNotificationsTest extends TestCase
{
    /** @var \Spatie\ServerMonitor\Models\Check */
    protected $check;

    public function setUp()
    {
        parent::setUp();

        $host = $this->createHost('localhost', 65000, ['diskspace']);

        $this->check = $host->checks->first();
    }

    /** @test */
    public function it_can_determine_that_it_is_not_throttling()
    {
        $this->assertFalse($this->check->isThrottlingFailedNotifications());
    }

    /** @test */
    public function it_can_can_start_a_throttling_period()
    {
        $this->check->startThrottlingFailedNotifications();

        $this->assertTrue($this->check->isThrottlingFailedNotifications());
    }

    /** @test */
    public function it_can_can_end_a_throttling_period()
    {
        $this->check->startThrottlingFailedNotifications();

        $this->check->stopThrottlingFailedNotifications();

        $this->assertFalse($this->check->isThrottlingFailedNotifications());
    }

    /** @test */
    public function the_throttling_period_will_end_after_an_amount_of_minutes()
    {
        $this->check->startThrottlingFailedNotifications();

        $minutes = $this->check->getDefinition()->throttleFailingNotificationsForMinutes();

        $this->assertGreaterThan(0, $minutes);

        $this->assertTrue($this->check->isThrottlingFailedNotifications());

        $this->progressMinutes($minutes - 1);

        $this->assertTrue($this->check->isThrottlingFailedNotifications());

        $this->progressMinutes(1);

        $this->assertFalse($this->check->isThrottlingFailedNotifications());
    }
}
