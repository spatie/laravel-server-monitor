<?php

namespace Spatie\ServerMonitor\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Spatie\ServerMonitor\Events\CheckFailed;
use Spatie\ServerMonitor\Events\CheckRestored;
use Spatie\ServerMonitor\Events\CheckSucceeded;
use Spatie\ServerMonitor\Events\CheckWarning;
use Spatie\ServerMonitor\CheckDefinitions\CheckDefinition;
use Spatie\ServerMonitor\Exceptions\InvalidCheckDefinition;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;
use Symfony\Component\Process\Process;

class Check extends Model
{
    public $guarded = [];

    public $casts = [
        'properties' => 'array',
    ];

    public $dates = [
        'checked_at', 'next_check_at',
    ];

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function getAttribute($key)
    {
        if (array_key_exists($key, $this->attributes)) {
            return parent::getAttribute($key);
        }

        $properties = json_decode($this->attributes['properties'], true);

        return Arr::get($properties, $key, parent::getAttribute($key));
    }

    public function shouldRun(): bool
    {
        if (!$this->enabled) {
            return false;
        }

        if (is_null($this->checked_at)) {
            return true;
        }

        return $this->checked_at
            ->addMinutes($this->next_check_in_minutes)
            ->isPast();
    }

    public function getDefinition(): CheckDefinition
    {
        if (!$definitionClass = config("server-monitor.checks.{$this->type}")) {
            throw InvalidCheckDefinition::unknownCheckType($this);
        }

        if (!class_exists($definitionClass)) {
            throw InvalidCheckDefinition::definitionClassDoesNotExist($this, $definitionClass);
        }

        return app($definitionClass)->setCheck($this);
    }

    public function getProcess(): Process
    {
        static $processes = [];

        if (!isset($processes[$this->id])) {

            $processes[$this->id] = new Process($this->getProcessCommand());
        }

        return $processes[$this->id];
    }

    public function getProcessCommand(): string
    {
        $delimiter = 'EOF-LARAVEL-SERVER-MONITOR';

        $definition = $this->getDefinition();

        $portArgument = empty($this->host->port) ? '' : "-p {$this->host->port}";

        return "ssh {$this->getTarget()} {$portArgument} 'bash -se <<$delimiter" . PHP_EOL
            . 'set -e' . PHP_EOL
            . $definition->getCommand() . PHP_EOL
            . $delimiter . "'";
    }

    protected function getTarget(): string
    {
        $target = $this->host->name;

        if ($this->host->ssh_user) {
            $target = $this->host->ssh_user . '@' . $target;
        }

        return $target;
    }

    public function succeeded(string $message = '')
    {
        $this->status = CheckStatus::SUCCESS;
        $this->message = $message;

        $this->save();

        event(new CheckSucceeded($this, $message));

        return $this;
    }


    public function warn(string $warningMessage = '')
    {
        $this->status = CheckStatus::WARNING;
        $this->message = $warningMessage;

        $this->save();

        event(new CheckWarning($this, $warningMessage));

        return $this;
    }

    public function failed(string $failureReason = '')
    {
        $this->status = CheckStatus::FAILED;
        $this->message = $failureReason;

        $this->save();

        event(new CheckFailed($this, $failureReason));

        return $this;
    }


    public function scopeEnabled(Builder $query)
    {
        $query->where('enabled', 1);
    }

    public function handleFinishedProcess()
    {
        $originalStatus = $this->status;

        $this->getDefinition()->handleFinishedProcess($this->getProcess());

        $this->scheduleNextRun();

        if ($this->shouldFireRestoredEvent($originalStatus, $this->status)) {
            event(new CheckRestored($this, $this->message));
        }

        return $this;
    }

    protected function scheduleNextRun()
    {
        $this->checked_at = Carbon::now();

        $this->next_check_in_minutes = $this->getDefinition()->performNextRunInMinutes();
        $this->save();

        return $this;
    }

    public function hasStatus(string $status): bool
    {
        return $this->status === $status;
    }

    protected function shouldFireRestoredEvent(?string $originalStatus, ?string $newStatus)
    {
        if (!in_array($originalStatus, [CheckStatus::FAILED, CheckStatus::WARNING])) {
            return false;
        }

        return $newStatus === CheckStatus::SUCCESS;
    }
}
