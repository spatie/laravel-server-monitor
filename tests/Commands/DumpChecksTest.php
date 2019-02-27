<?php

namespace Spatie\ServerMonitor\Test\Commands;

use Artisan;
use Spatie\ServerMonitor\Test\TestCase;

class DumpChecksTest extends TestCase
{
    private $tempFile = __DIR__.'/temp.json';

    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        if (file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
    }

    /** @test */
    public function it_creates_an_empty_file_for_an_empty_database()
    {
        Artisan::call('server-monitor:dump-checks', ['path' => $this->tempFile]);

        $this->assertFileExists($this->tempFile);
        $contents = file_get_contents($this->tempFile);
        $this->assertJson($contents);
        $this->assertJsonStringEqualsJsonString($contents, '[]');
    }

    /** @test */
    public function it_creates_the_same_output_for_synced_file()
    {
        Artisan::call('server-monitor:sync-file', ['path' => __DIR__.'/../stubs/file-sync-original.json']);
        Artisan::call('server-monitor:dump-checks', ['path' => $this->tempFile]);

        $this->assertFileExists($this->tempFile);
        $this->assertJsonFileEqualsJsonFile(__DIR__.'/../stubs/file-sync-original.json', $this->tempFile);
    }
}
