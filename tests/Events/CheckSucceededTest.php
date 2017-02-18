<?php

namespace Spatie\ServerMonitor\Test\Events;

use Spatie\ServerMonitor\Test\TestCase;
use Spatie\ServerMonitor\Events\CheckSucceeded;

class CheckSucceededTest extends TestCase
{
    /** @var \Spatie\ServerMonitor\Models\Check */
    protected $check;

    /** @var \Spatie\ServerMonitor\CheckDefinitions\Diskspace */
    protected $diskspaceCheckDefinition;

    public function setUp()
    {
        parent::setUp();

        Event::fake();

        $this->check = $this
            ->createHost('localhost', 65000, ['diskspace'])
            ->checks
            ->first();

        $this->diskspaceCheckDefinition = (new Diskspace())->setCheck($this->check);
    }

    /** @test */
    public function the_check_succeeded_event_will_be_fired_when_a_check_succeeds()
    {
        $process = $this->getSuccessfulProcessWithOutput(
            'Filesystem 512-blocks      Used Available Capacity  Mounted on\n'.
            '/dev/disk1  974700800 830137776 144051024   40%    /'
        );

        $this->diskspaceCheckDefinition->handleSuccessfulProcess($process);

        Event::assertDispatched(CheckSucceeded::class, function (CheckSucceeded $event) {
            return $event->check->id === $this->check->id;
        });
    }
}
