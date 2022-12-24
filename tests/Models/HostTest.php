<?php

use Spatie\ServerMonitor\Models\Check;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;
use Spatie\ServerMonitor\Models\Enums\HostHealth;
use Spatie\ServerMonitor\Models\Host;

function createHostWithCheckStatuses(array $statuses): Host
{
    $host = Host::create([
        'name' => 'hostname',
    ]);

    $host->checks()->saveMany(collect($statuses)->map(function (string $status) {
        return new Check([
            'type' => 'my-check-'.rand(),
            'status' => $status,
        ]);
    }));

    return $host;
}

function createHostWithCheckTypes(array $types): Host
{
    $host = Host::create([
        'name' => 'hostname',
    ]);

    $host->checks()->saveMany(collect($types)->map(function (string $type) {
        return new Check([
            'type' => $type,
            'status' => CheckStatus::SUCCESS,
        ]);
    }));

    return $host;
}

it('its status will be a warning when it has no checks', function () {
    $host = createHostWithCheckStatuses([]);

    expect($host->status === HostHealth::WARNING)->toBeTrue();
});

it('will determine that it is healthy when all its checks have succeeded', function () {
    $host = createHostWithCheckStatuses([
        CheckStatus::SUCCESS,
    ]);

    expect($host->status === HostHealth::HEALTHY)->toBeTrue();
});

it('will determine that it is unhealthy when one of its checks has failed', function () {
    $host = createHostWithCheckStatuses([
        CheckStatus::SUCCESS, CheckStatus::FAILED, CheckStatus::WARNING, CheckStatus::NOT_YET_CHECKED,
    ]);

    expect($host->status === HostHealth::UNHEALTHY)->toBeTrue();
});

it('its status will be a warning when it contains an check that has not run yet', function () {
    $host = createHostWithCheckStatuses([
        CheckStatus::SUCCESS, CheckStatus::NOT_YET_CHECKED,
    ]);

    expect($host->status === HostHealth::WARNING)->toBeTrue();
});

it('its status will be a warning when it contains an check that issued a warning', function () {
    $host = createHostWithCheckStatuses([
        CheckStatus::SUCCESS, CheckStatus::WARNING,
    ]);

    expect($host->status === HostHealth::WARNING)->toBeTrue();
});

it('has helper methods to determine its status', function () {
    $host = createHostWithCheckStatuses([
        CheckStatus::SUCCESS,
    ]);

    expect($host->isHealthy())->toBeTrue();
    expect($host->isUnhealthy())->toBeFalse();
    expect($host->hasWarning())->toBeFalse();

    $host = createHostWithCheckStatuses([
        CheckStatus::WARNING,
    ]);

    expect($host->isHealthy())->toBeFalse();
    expect($host->isUnhealthy())->toBeFalse();
    expect($host->hasWarning())->toBeTrue();

    $host = createHostWithCheckStatuses([
        CheckStatus::FAILED,
    ]);

    expect($host->isHealthy())->toBeFalse();
    expect($host->isUnhealthy())->toBeTrue();
    expect($host->hasWarning())->toBeFalse();
});

it('can determine if it has a check with a certain type', function () {
    $host = createHostWithCheckTypes(['check-1', 'check-2']);

    expect($host->hasCheckType('check-1'))->toBeTrue();
    expect($host->hasCheckType('check-2'))->toBeTrue();
    expect($host->hasCheckType('check-3'))->toBeFalse();
});
