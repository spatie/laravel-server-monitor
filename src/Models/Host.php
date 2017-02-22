<?php

namespace Spatie\ServerMonitor\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;
use Spatie\ServerMonitor\Models\Enums\HostHealth;

class Host extends Model
{
    public $guarded = [];

    public function checks(): HasMany
    {
        return $this->hasMany(Check::class);
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
}
