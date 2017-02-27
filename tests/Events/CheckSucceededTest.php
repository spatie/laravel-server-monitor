<?php

namespace Spatie\ServerMontior\Test\Events;

use Event;
use Illuminate\Support\Facades\Artisan;
use Spatie\ServerMonitor\Test\TestCase;
use Spatie\ServerMonitor\Events\CheckSucceeded;

class CheckSucceededTest extends TestCase
{
    /** @var \Spatie\ServerMonitor\Models\Check */
    protected $check;

    public function setUp()
    {
        parent::setUp();

        $this->skipIfDummySshServerIsNotRunning();

        Event::fake();

        $this->check = $this->createHost()->checks->first();
    }

    /** @test */
    public function the_succeeded_event_will_be_fired_when_a_check_succeeds()
    {
        $this->letSshServerRespondWithDiskspaceUsagePercentage(20);

        Event::assertNotDispatched(CheckSucceeded::class);

        Artisan::call('server-monitor:run-checks');

        Event::assertDispatched(CheckSucceeded::class, function (CheckSucceeded $event) {
            return $event->check->id === $this->check->id;
        });
    }
}
