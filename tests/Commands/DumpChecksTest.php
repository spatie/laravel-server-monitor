<?php

use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    $this->tempFile = __DIR__.'/temp.json';
});

afterEach(function () {
    if (file_exists($this->tempFile)) {
        unlink($this->tempFile);
    }
});

it('creates an empty file for an empty database', function () {
    Artisan::call('server-monitor:dump-checks', ['path' => $this->tempFile]);

    expect($this->tempFile)->toBeFile();
    $contents = file_get_contents($this->tempFile);
    expect($contents)->toBeJson();
    $this->assertJsonStringEqualsJsonString($contents, '[]');
});

it('creates the same output for synced file', function () {
    Artisan::call('server-monitor:sync-file', ['path' => __DIR__.'/../stubs/file-sync-original.json']);
    Artisan::call('server-monitor:dump-checks', ['path' => $this->tempFile]);

    expect($this->tempFile)->toBeFile();
    $this->assertJsonFileEqualsJsonFile(__DIR__.'/../stubs/file-sync-original.json', $this->tempFile);
});
