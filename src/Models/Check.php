<?php

namespace Spatie\ServerMonitor\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Spatie\CheckSucceeded\Events\CheckFailed;
use Spatie\CheckSucceeded\Events\CheckSucceeded;
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

        return $this->next_check_at->isPast();
    }

    public function getTarget(): string
    {
        $target = $this->host->url;

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
        static $process = null;

        if (is_null($process)) {

            $delimiter = 'EOF-LARAVEL-SERVER-MONITOR';

            $definition = $this->getDefinition();

            $process = new Process(
                "ssh {$this->getTarget()} 'bash -se' << \\$delimiter".PHP_EOL
                .'set -e'.PHP_EOL
                .$definition->getCommand().PHP_EOL
                .$delimiter
            );
        }

        return $process;
    }

    public function failed(string $failureReason)
    {
        $this->status = CheckStatus::FAILURE;
        $this->message = $failureReason;

        $this->save();

        event(new CheckFailed($this, $failureReason));

        return $this;
    }

    public function succeeded()
    {
        $this->status = CheckStatus::SUCCESS;
        $this->message = '';

        $this->save();

        event(new CheckSucceeded($this));

        return $this;
    }

    public function scopeEnabled(Builder $query)
    {
        $query->where('enabled', 1);
    }
}