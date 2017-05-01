<?php

namespace Spatie\ServerMonitor\Models;

use Carbon\Carbon;
use Symfony\Component\Process\Process;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Spatie\ServerMonitor\Events\CheckRestored;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;
use Spatie\ServerMonitor\Models\Concerns\HasProcess;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\ServerMonitor\CheckDefinitions\CheckDefinition;
use Spatie\ServerMonitor\Models\Presenters\CheckPresenter;
use Spatie\ServerMonitor\Exceptions\InvalidCheckDefinition;
use Spatie\ServerMonitor\Models\Concerns\HandlesCheckResult;
use Spatie\ServerMonitor\Models\Concerns\HasCustomProperties;
use Spatie\ServerMonitor\Models\Concerns\ThrottlesFailingNotifications;

class Check extends Model
{
    use CheckPresenter,
        HasCustomProperties,
        ThrottlesFailingNotifications,
        HasProcess,
        HandlesCheckResult;

    public $guarded = [];

    public $casts = [
        'custom_properties' => 'array',
        'last_run_output' => 'array',
    ];

    public $dates = [
        'last_ran_at', 'next_check_at', 'started_throttling_failing_notifications_at',
    ];

    public function host(): BelongsTo
    {
        return $this->belongsTo(config('server-monitor.host_model', Host::class));
    }

    public function scopeHealthy($query)
    {
        return $query->where('status', CheckStatus::SUCCESS);
    }

    public function scopeUnhealthy($query)
    {
        return $query->where('status', '!=', CheckStatus::SUCCESS);
    }

    public function scopeEnabled(Builder $query)
    {
        $query->where('enabled', 1);
    }

    public function shouldRun(): bool
    {
        if (! $this->enabled) {
            return false;
        }

        if (is_null($this->last_ran_at)) {
            return true;
        }

        return ! $this->last_ran_at
            ->addMinutes($this->next_run_in_minutes)
            ->isFuture();
    }

    public function getDefinition(): CheckDefinition
    {
        if (! $definitionClass = config("server-monitor.checks.{$this->type}")) {
            throw InvalidCheckDefinition::unknownCheckType($this);
        }

        if (! class_exists($definitionClass)) {
            throw InvalidCheckDefinition::definitionClassDoesNotExist($this, $definitionClass);
        }

        return app($definitionClass)->setCheck($this);
    }

    public function handleFinishedProcess()
    {
        $originalStatus = $this->status;

        $this->getDefinition()->determineResult($this->getProcess());

        $this->scheduleNextRun();

        if ($this->shouldFireRestoredEvent($originalStatus, $this->status)) {
            event(new CheckRestored($this));
        }

        return $this;
    }

    protected function shouldFireRestoredEvent(?string $originalStatus, ?string $newStatus)
    {
        if (! in_array($originalStatus, [CheckStatus::FAILED, CheckStatus::WARNING])) {
            return false;
        }

        return $newStatus === CheckStatus::SUCCESS;
    }

    protected function scheduleNextRun()
    {
        $this->last_ran_at = Carbon::now();

        $this->next_run_in_minutes = $this->getDefinition()->performNextRunInMinutes();
        $this->save();

        return $this;
    }

    public function hasStatus(string $status): bool
    {
        return $this->status === $status;
    }

    public function storeProcessOutput(Process $process)
    {
        $this->last_run_output = [
            'output' => $process->getOutput(),
            'error_output' => $process->getErrorOutput(),
            'exit_code' => $process->getExitCode(),
            'exit_code_text' => $process->getExitCodeText(),
        ];

        $this->save();
    }
}
