<?php

namespace Spatie\ServerMonitor\CheckDefinitions\Test;

use Spatie\ServerMonitor\Models\Check;
use Spatie\ServerMonitor\Test\TestCase;
use Spatie\ServerMonitor\CheckDefinitions\MySql;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;
use Spatie\ServerMonitor\CheckDefinitions\Elasticsearch;

class MySqlTest extends TestCase
{
    /** @var \Spatie\ServerMonitor\CheckDefinitions\MySql */
    protected $mySqlCheckDefintion;

    /** @var \Spatie\ServerMonitor\Models\Check */
    protected $check;

    public function setUp()
    {
        parent::setUp();

        $this->createHost('localhost', 65000, ['elasticsearch']);

        $this->check = Check::first();

        $this->mySqlCheckDefintion = (new MySql())->setCheck($this->check);
    }

    /** @test */
    public function it_can_determine_success()
    {
        $process = $this->getSuccessfulProcessWithOutput(
            '1410 ?        00:20:36 mysqld'
        );

        $this->mySqlCheckDefintion->resolve($process);

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

        $this->mySqlCheckDefintion->resolve($process);

        $this->check->fresh();

        $this->assertStringContains('is not running', $this->check->last_run_message);
        $this->assertEquals(CheckStatus::FAILED, $this->check->status);
    }
}
