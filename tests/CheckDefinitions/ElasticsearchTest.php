<?php

namespace Spatie\ServerMonitor\CheckDefinitions\Test;

use Spatie\ServerMonitor\Models\Check;
use Spatie\ServerMonitor\Test\TestCase;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;
use Spatie\ServerMonitor\CheckDefinitions\Elasticsearch;

class ElasticsearchTest extends TestCase
{
    /** @var \Spatie\ServerMonitor\CheckDefinitions\Elasticsearch */
    protected $elasticsearchDefintion;

    /** @var \Spatie\ServerMonitor\Models\Check */
    protected $check;

    public function setUp()
    {
        parent::setUp();

        $this->createHost('localhost', 65000, ['elasticsearch']);

        $this->check = Check::first();

        $this->elasticsearchDefintion = (new Elasticsearch())->setCheck($this->check);
    }

    /** @test */
    public function it_can_determine_success()
    {
        $process = $this->getSuccessfulProcessWithOutput(
            'output something something lucene_version something something'
        );

        $this->elasticsearchDefintion->resolve($process);

        $this->check->fresh();

        $this->assertStringContains('is running', $this->check->last_run_message);
        $this->assertEquals(CheckStatus::SUCCESS, $this->check->status);
    }

    /** @test */
    public function it_can_determine_failure()
    {
        $process = $this->getSuccessfulProcessWithOutput(
            'output something something something something'
        );

        $this->elasticsearchDefintion->resolve($process);

        $this->check->fresh();

        $this->assertStringContains('is not running', $this->check->last_run_message);
        $this->assertEquals(CheckStatus::FAILED, $this->check->status);
    }
}
