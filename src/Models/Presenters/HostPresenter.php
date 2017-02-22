<?php

namespace Spatie\ServerMonitor\Models\Presenters;

use Spatie\ServerMonitor\Helpers\Emoji;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;
use Spatie\ServerMonitor\Models\Enums\HostHealth;

trait HostPresenter
{
    public function getHealthAsEmojiAttribute(): string
    {
        if ($this->isHealthy()) {
            return Emoji::ok();
        }

        if ($this->isUnhealthy()) {
            return Emoji::notOk();
        }

        if ($this->hasWarning()) {
            return Emoji::warning();
        }

        throw new Exception("Could not determine health emoji for host `{$this->id}`");
    }
}
