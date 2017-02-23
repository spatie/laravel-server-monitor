<?php

namespace Spatie\ServerMonitor\Models;

use Carbon\Carbon;
use Spatie\ServerMonitor\Helpers\ConsoleOutput;
use Symfony\Component\Process\Process;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Spatie\ServerMonitor\Events\CheckFailed;
use Spatie\ServerMonitor\Events\CheckWarning;
use Spatie\ServerMonitor\Events\CheckRestored;
use Spatie\ServerMonitor\Events\CheckSucceeded;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\ServerMonitor\CheckDefinitions\CheckDefinition;
use Spatie\ServerMonitor\Models\Presenters\CheckPresenter;
use Spatie\ServerMonitor\Exceptions\InvalidCheckDefinition;
use Spatie\ServerMonitor\Models\Concerns\HasCustomProperties;

class Check extends Model
{
    use CheckPresenter, HasCustomProperties;

    public $guarded = [];

    public $casts = [
        'custom_properties' => 'array',
        'process_output' => 'array',
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

        $properties = json_decode($this->attributes['custom_properties'], true);

        return array_get($properties, $key, parent::getAttribute($key));
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
            $processes[$this->id] = new Process($this->getProcessCommand());
        }

        return $processes[$this->id];
    }

    public function getProcessCommand(): string
    {
        $delimiter = 'EOF-LARAVEL-SERVER-MONITOR';

        $definition = $this->getDefinition();

        $portArgument = empty($this->host->port) ? '' : "-p {$this->host->port}";

        return "ssh {$this->getTarget()} {$portArgument} 'bash -se <<$delimiter".PHP_EOL
            .'set -e'.PHP_EOL
            .$definition->getCommand().PHP_EOL
            .$delimiter."'";
    }

    protected function getTarget(): string
    {
        $target = $this->host->name;

        if ($this->host->ssh_user) {
            $target = $this->host->ssh_user.'@'.$target;
        }

        return $target;
    }

    public function succeeded(string $message = '')
    {
        $this->status = CheckStatus::SUCCESS;
        $this->message = $message;

        $this->save();

        event(new CheckSucceeded($this));
        ConsoleOutput::info($this->host->name.": check `{$this->type}` succeeded");

        return $this;
    }

    public function warn(string $warningMessage = '')
    {
        $this->status = CheckStatus::WARNING;
        $this->message = $warningMessage;

        $this->save();

        event(new CheckWarning($this));

        ConsoleOutput::info($this->host->name.": check `{$this->type}` issued warning");

        return $this;
    }

    public function failed(string $failureReason = '')
    {
        $this->status = CheckStatus::FAILED;
        $this->message = $failureReason;

        $this->save();

        event(new CheckFailed($this));

        ConsoleOutput::error($this->host->name.": check `{$this->type}` failed");

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
            event(new CheckRestored($this));
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
        if (! in_array($originalStatus, [CheckStatus::FAILED, CheckStatus::WARNING])) {
            return false;
        }

        return $newStatus === CheckStatus::SUCCESS;
    }

    public function storeProcessOutput(Process $process)
    {
        $this->process_output = [
            'output' => $process->getOutput(),
            'error_output' => $process->getErrorOutput(),
            'exit_code' => $process->getExitCode(),
            'exit_code_text' => $process->getExitCodeText(),
        ];

        $this->save();
    }
}
