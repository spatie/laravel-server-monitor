<?php

namespace Spatie\ServerMonitor\CheckDefinitions\Test;

use Spatie\ServerMonitor\Models\Check;
use Spatie\ServerMonitor\Test\TestCase;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;
use Spatie\ServerMonitor\CheckDefinitions\Memcached;
use Spatie\ServerMonitor\CheckDefinitions\Elasticsearch;

class MemcachedTest extends TestCase
{
    /** @var \Spatie\ServerMonitor\CheckDefinitions\Memcached */
    protected $memcachedDefinition;

    /** @var \Spatie\ServerMonitor\Models\Check */
    protected $check;

    public function setUp()
    {
        parent::setUp();

        $this->createHost('localhost', 65000, ['elasticsearch']);

        $this->check = Check::first();

        $this->memcachedDefinition = (new Memcached())->setCheck($this->check);
    }

    /** @test */
    public function it_can_determine_success()
    {
        $process = $this->getSuccessfulProcessWithOutput(
            '* memcached is running'
        );

        $this->memcachedDefinition->resolve($process);

        $this->check->fresh();

        $this->assertStringContains('is running', $this->check->last_run_message);
        $this->assertEquals(CheckStatus::SUCCESS, $this->check->status);
    }

    /** @test */
    public function it_can_determine_failure()
    {
        $process = $this->getSuccessfulProcessWithOutput(
            ''
        );

        $this->memcachedDefinition->resolve($process);

        $this->check->fresh();

        $this->assertStringContains('is not running', $this->check->last_run_message);
        $this->assertEquals(CheckStatus::FAILED, $this->check->status);
    }
}
