<?php

namespace Spatie\ServerMonitor\CheckDefinitions\Test;

use Spatie\ServerMonitor\Models\Check;
use Spatie\ServerMonitor\Test\TestCase;
use Spatie\ServerMonitor\CheckDefinitions\Redis;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;

class RedisTest extends TestCase
{
    /** @var \Spatie\ServerMonitor\CheckDefinitions\Redis */
    protected $redisDefintion;

    /** @var \Spatie\ServerMonitor\Models\Check */
    protected $check;

    public function setUp()
    {
        parent::setUp();

        $this->createHost('localhost', 65000, ['redis']);

        $this->check = Check::first();

        $this->redisDefintion = (new Redis())->setCheck($this->check);
    }

    /** @test */
    public function it_can_determine_success()
    {
        $process = $this->getSuccessfulProcessWithOutput(
            'PONG'
        );

        $this->redisDefintion->resolve($process);

        $this->check->fresh();

        $this->assertStringContains('is running', $this->check->last_run_message);
        $this->assertEquals(CheckStatus::SUCCESS, $this->check->status);
    }

    /** @test */
    public function it_can_determine_failure()
    {
        $process = $this->getSuccessfulProcessWithOutput(
            'NO PONG'
        );

        $this->redisDefintion->resolve($process);

        $this->check->fresh();

        $this->assertStringContains('is not running', $this->check->last_run_message);
        $this->assertEquals(CheckStatus::FAILED, $this->check->status);
    }
}
