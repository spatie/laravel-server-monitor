<?php

namespace Spatie\ServerMonitor\Test\Commands;

use Artisan;
use Spatie\ServerMonitor\Models\Host;
use Spatie\ServerMonitor\Test\TestCase;

class DumpChecksTest extends TestCase
{
    private $tempFile = __DIR__ . '/temp.json';

    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
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
        Artisan::call('server-monitor:sync-file', ['path' => __DIR__ . '/../stubs/file-sync-original.json']);
        Artisan::call('server-monitor:dump-checks', ['path' => $this->tempFile]);
        $this->assertFileExists($this->tempFile);
        $this->assertJsonFileEqualsJsonFile(__DIR__ . '/../stubs/file-sync-original.json', $this->tempFile);
    }

//    /** @test */
//    public function it_can_update_hosts()
//    {
//        Artisan::call('server-monitor:sync-file', ['path' => __DIR__.'/../stubs/file-sync-update.json']);
//
//        $updatedHost = Host::where('name', 'original-host')->first();
//
//        $this->seeInConsoleOutput('Synced 1 host(s) to database');
//        $this->seeInConsoleOutput('Deleted `test-check` from host `original-host`');
//
//        $this->assertEquals('root-updated', $updatedHost->ssh_user);
//
//        $this->assertTrue($updatedHost->checks->contains('type', 'test-check-updated'));
//
//        $this->assertFalse($updatedHost->checks->contains('type', 'test-check'));
//    }
//
//    /** @test */
//    public function it_can_delete_hosts_not_found_in_file()
//    {
//        Artisan::call('server-monitor:sync-file', [
//            'path' => __DIR__.'/../stubs/file-sync-original.json',
//            '--delete-missing' => true,
//        ]);
//
//        $deletedHost = Host::where('name', 'original-host')->first();
//
//        $this->seeInConsoleOutput('Deleted host `original-host`');
//
//        $this->assertEmpty($deletedHost);
//    }
}
