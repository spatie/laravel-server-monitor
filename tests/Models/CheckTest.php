<?php

namespace Spatie\ServerMonitor\Test\Models;

use Carbon\Carbon;
use Spatie\ServerMonitor\Models\Check;
use Spatie\ServerMonitor\Test\TestCase;
use Spatie\ServerMonitor\CheckDefinitions\Diskspace;
use Spatie\ServerMonitor\Exceptions\InvalidCheckDefinition;

class CheckTest extends TestCase
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
    public function it_can_get_its_definition()
    {
        $this->assertInstanceOf(Diskspace::class, $this->check->getDefinition());
    }

    /** @test */
    public function it_will_throw_an_exception_when_it_has_an_unknown_type()
    {
        $this->check->type = 'bla bla';
        $this->check->save();

        $this->expectException(InvalidCheckDefinition::class);

        $this->check->getDefinition();
    }

    /** @test */
    public function it_will_determine_that_it_should_be_run()
    {
        $this->assertTrue($this->check->shouldRun());
    }

    /** @test */
    public function it_will_determine_that_it_should_not_run_when_it_is_disabled()
    {
        $this->check->enabled = false;

        $this->check->save();

        $this->assertFalse($this->check->shouldRun());
    }

    /** @test */
    public function it_will_determine_that_it_should_not_be_run_until_after_a_certain_period_of_time()
    {
        $nextRunInMinutes = 5;

        $this->check->last_ran_at = Carbon::now();

        $this->check->next_run_in_minutes = $nextRunInMinutes;

        $this->check->save();

        foreach (range(1, $nextRunInMinutes) as $pastMinutes) {
            $this->assertFalse($this->check->shouldRun());

            $this->progressMinutes(1);
        }

        $this->assertTrue($this->check->shouldRun());
    }
}
