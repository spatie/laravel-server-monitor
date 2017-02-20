<?php

namespace Spatie\ServerMontior\Test\Events;

use Event;
use Illuminate\Support\Facades\Artisan;
use Spatie\ServerMonitor\Events\CheckFailed;
use Spatie\ServerMonitor\Events\CheckRestored;
use Spatie\ServerMonitor\Events\CheckSucceeded;
use Spatie\ServerMonitor\Test\TestCase;
use Spatie\UptimeMonitor\MonitorRepository;

class CheckFailedTest extends TestCase
{
    /** @var \Spatie\ServerMonitor\Models\Check */
    protected $check;

    public function setUp()
    {
        parent::setUp();

        Event::fake();

        $this->check = $this->createHost()->checks->first();
    }

    /** @test */
    public function the_faield_event_will_be_fired_when_a_check_failed()
    {
        $this->letSshServerRespondWithDiskspaceUsagePercentage(99);

        Event::assertNotDispatched(CheckFailed::class);

        Artisan::call('monitor:run-checks');

        Event::assertDispatched(CheckFailed::class, function (CheckFailed $event) {
            return $event->check->id === $this->check->id;
        });
    }
}
