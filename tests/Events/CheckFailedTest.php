<?php

namespace Spatie\ServerMontior\Test\Events;

use Event;
use Illuminate\Support\Facades\Artisan;
use Spatie\ServerMonitor\Test\TestCase;
use Spatie\ServerMonitor\Events\CheckFailed;

class CheckFailedTest extends TestCase
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
    public function the_faield_event_will_be_fired_when_a_check_failed()
    {
        $this->letSshServerRespondWithDiskspaceUsagePercentage(99);

        Event::assertNotDispatched(CheckFailed::class);

        Artisan::call('server-monitor:run-checks');

        Event::assertDispatched(CheckFailed::class, function (CheckFailed $event) {
            return $event->check->id === $this->check->id;
        });
    }
}
