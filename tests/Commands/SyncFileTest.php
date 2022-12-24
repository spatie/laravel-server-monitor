<?php

use Illuminate\Support\Facades\Artisan;
use Spatie\ServerMonitor\Models\Host;

beforeEach(function () {
    Host::create([
        'name' => 'original-host',
        'ssh_user' => 'root',
        'port' => 22,
    ])->checks()->create([
        'type' => 'test-check',
    ]);
});

it('can create hosts', function () {
    Artisan::call('server-monitor:sync-file', ['path' => __DIR__.'/../stubs/file-sync-original.json']);

    $importHostOne = Host::where('name', 'test-import-one')->first();
    $importHostTwo = Host::where('name', 'test-import-two')->first();

    $this->seeInConsoleOutput('Synced 2 host(s) to database');

    expect($importHostOne->ssh_user)->toEqual('root');
    expect($importHostTwo->ssh_user)->toEqual('root');

    expect($importHostOne->checks->contains('type', 'test-check-one'))->toBeTrue();
    expect($importHostTwo->checks->contains('type', 'test-check-one'))->toBeTrue();
    expect($importHostTwo->checks->contains('type', 'test-check-two'))->toBeTrue();
});

it('can update hosts', function () {
    Artisan::call('server-monitor:sync-file', ['path' => __DIR__.'/../stubs/file-sync-update.json']);

    $updatedHost = Host::where('name', 'original-host')->first();

    $this->seeInConsoleOutput('Synced 1 host(s) to database');
    $this->seeInConsoleOutput('Deleted `test-check` from host `original-host`');

    expect($updatedHost->ssh_user)->toEqual('root-updated');

    expect($updatedHost->checks->contains('type', 'test-check-updated'))->toBeTrue();

    expect($updatedHost->checks->contains('type', 'test-check'))->toBeFalse();
});

it('can delete hosts not found in file', function () {
    Artisan::call('server-monitor:sync-file', [
        'path' => __DIR__.'/../stubs/file-sync-original.json',
        '--delete-missing' => true,
    ]);

    $deletedHost = Host::where('name', 'original-host')->first();

    $this->seeInConsoleOutput('Deleted host `original-host`');

    expect($deletedHost)->toBeEmpty();
});
