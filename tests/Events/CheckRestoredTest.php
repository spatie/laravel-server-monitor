<?php

namespace Spatie\ServerMontior\Test\Events;

use Event;
use Illuminate\Support\Facades\Artisan;
use Spatie\ServerMonitor\Events\CheckRestored;
use Spatie\ServerMonitor\Test\TestCase;
use Spatie\UptimeMonitor\MonitorRepository;

class CheckRecoveredTest extends TestCase
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
    public function the_recovered_event_will_be_fired_when_an_check_succeeds_after_it_has_failed()
    {
        $this->letSshServerRespondWithDiskspaceUsagePercentage(99);

        Artisan::call('monitor:run-checks');

        $this->letSshServerRespondWithDiskspaceUsagePercentage(20);

        $this->progressMinutes(60 * 24);

        Event::assertNotDispatched(CheckRestored::class);

        Artisan::call('monitor:run-checks');

        Event::assertDispatched(CheckRestored::class, function (CheckRestored $event) {
            return $event->check->id === $this->check->id;
        });
    }
}
