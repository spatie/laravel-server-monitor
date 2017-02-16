<?php

namespace Spatie\ServerMonitor\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Spatie\ServerMonitor\Events\CheckFailed;
use Spatie\ServerMonitor\Events\CheckSucceeded;
use Spatie\ServerMonitor\Events\CheckWarning;
use Spatie\ServerMonitor\CheckDefinitions\CheckDefinition;
use Spatie\ServerMonitor\Exceptions\InvalidCheckDefinition;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;
use Symfony\Component\Process\Process;

class Check extends Model
{
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
        if (! $this->enabled) {
            return false;
        }

        if (is_null($this->checked_at)) {
            return true;
        }

        return $this->checked_at
            ->addMinutes($this->next_check_in_minutes)
            ->isPast();
    }

    public function getTarget(): string
    {
        $target = $this->host->name;

        if ($this->host->ssh_user) {
            $target = $this->host->ssh_user . '@' . $target;
        }

        return $target;
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

    public function getProcess(): Process
    {
        static $processes = [];

        if (! isset($processes[$this->id])) {

            $delimiter = 'EOF-LARAVEL-SERVER-MONITOR';

            $definition = $this->getDefinition();

            $processes[$this->id] = new Process(
                "ssh {$this->getTarget()} 'bash -se' << \\$delimiter".PHP_EOL
                .'set -e'.PHP_EOL
                .$definition->getCommand().PHP_EOL
                .$delimiter
            );
        }

        return $processes[$this->id];
    }

    public function succeeded(string $message = '')
    {
        $this->status = CheckStatus::SUCCESS;
        $this->message = $message;

        $this->save();

        event(new CheckSucceeded($this));

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
        $this->getDefinition()->handleFinishedProcess($this->getProcess());

        $this->scheduleNextRun();

        return $this;
    }

    protected function scheduleNextRun()
    {
        $this->checked_at = \Carbon\Carbon::now();

        $this->next_check_in_minutes = $this->getDefinition()->performNextRunInMinutes();
        $this->save();

        return $this;
    }

    public function hasStatus(string $status): bool
    {
        return $this->status === $status;
    }
}