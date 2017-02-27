<?php

namespace Spatie\ServerMontior\Test\Events;

use Event;
use Illuminate\Support\Facades\Artisan;
use Spatie\ServerMonitor\Test\TestCase;
use Spatie\ServerMonitor\Events\CheckRestored;

class CheckRestoredTest extends TestCase
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
    public function the_recovered_event_will_be_fired_when_an_check_succeeds_after_it_has_failed()
    {
        $this->letSshServerRespondWithDiskspaceUsagePercentage(99);

        Artisan::call('server-monitor:run-checks');

        $this->letSshServerRespondWithDiskspaceUsagePercentage(20);

        $this->progressMinutes(60 * 24);

        Event::assertNotDispatched(CheckRestored::class);

        Artisan::call('server-monitor:run-checks');

        Event::assertDispatched(CheckRestored::class, function (CheckRestored $event) {
            return $event->check->id === $this->check->id;
        });
    }
}
