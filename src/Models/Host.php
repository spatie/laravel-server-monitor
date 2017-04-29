<?php

namespace Spatie\ServerMonitor\Models;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Spatie\ServerMonitor\Models\Enums\HostHealth;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\ServerMonitor\Models\Presenters\HostPresenter;
use Spatie\ServerMonitor\Models\Concerns\HasCustomProperties;

class Host extends Model
{
    use HostPresenter, HasCustomProperties;

    public $casts = [
        'custom_properties' => 'array',
    ];

    public $guarded = [];

    public function checks(): HasMany
    {
        return $this->hasMany(config('server-monitor.check_model', Check::class));
    }

    public function getEnabledChecksAttribute(): Collection
    {
        return $this->checks()->enabled()->get();
    }

    public function isHealthy(): bool
    {
        return $this->status === HostHealth::HEALTHY;
    }

    public function isUnhealthy(): bool
    {
        return $this->status === HostHealth::UNHEALTHY;
    }

    public function hasWarning(): bool
    {
        return $this->status === HostHealth::WARNING;
    }

    public function getStatusAttribute(): string
    {
        if ($this->enabled_checks->count() === 0) {
            return HostHealth::WARNING;
        }

        if ($this->enabled_checks->contains->hasStatus(CheckStatus::FAILED)) {
            return HostHealth::UNHEALTHY;
        }

        if ($this->enabled_checks->every->hasStatus(CheckStatus::SUCCESS)) {
            return HostHealth::HEALTHY;
        }

        return HostHealth::WARNING;
    }

    public function hasCheckType(string $type): bool
    {
        return $this->checks->contains(function (Check $check) use ($type) {
            return $check->type === $type;
        });
    }
}
