<?php

namespace Spatie\ServerMonitor\Test\Commands;

use Artisan;
use Spatie\ServerMonitor\Models\Host;
use Spatie\ServerMonitor\Test\TestCase;

class SyncFileTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        Host::create([
            'name' => 'original-host',
            'ssh_user' => 'root',
            'port' => 22,
        ])->checks()->create([
            'type' => 'test-check',
        ]);
    }

    /** @test */
    public function it_can_create_hosts()
    {
        Artisan::call('server-monitor:sync-file', ['path' => __DIR__.'/../stubs/file-sync-original.json']);

        $importHostOne = Host::where('name', 'test-import-one')->first();
        $importHostTwo = Host::where('name', 'test-import-two')->first();

        $this->seeInConsoleOutput('Synced 2 host(s) to database');

        $this->assertEquals('root', $importHostOne->ssh_user);
        $this->assertEquals('root', $importHostTwo->ssh_user);

        $this->assertTrue($importHostOne->checks->contains('type', 'test-check-one'));
        $this->assertTrue($importHostTwo->checks->contains('type', 'test-check-one'));
        $this->assertTrue($importHostTwo->checks->contains('type', 'test-check-two'));
    }

    /** @test */
    public function it_can_update_hosts()
    {
        Artisan::call('server-monitor:sync-file', ['path' => __DIR__.'/../stubs/file-sync-update.json']);

        $updatedHost = Host::where('name', 'original-host')->first();

        $this->seeInConsoleOutput('Synced 1 host(s) to database');
        $this->seeInConsoleOutput('Deleted `test-check` from host `original-host`');

        $this->assertEquals('root-updated', $updatedHost->ssh_user);

        $this->assertTrue($updatedHost->checks->contains('type', 'test-check-updated'));

        $this->assertFalse($updatedHost->checks->contains('type', 'test-check'));
    }

    /** @test */
    public function it_can_delete_hosts_not_found_in_file()
    {
        Artisan::call('server-monitor:sync-file', [
            'path' => __DIR__.'/../stubs/file-sync-original.json',
            '--delete-missing' => true,
        ]);

        $deletedHost = Host::where('name', 'original-host')->first();

        $this->seeInConsoleOutput('Deleted host `original-host`');

        $this->assertEmpty($deletedHost);
    }
}
