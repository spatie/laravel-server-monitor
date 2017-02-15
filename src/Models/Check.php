<?php

namespace Spatie\ServerMonitor\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Spatie\ServerMonitor\CheckDefinitions\CheckDefinition;
use Spatie\ServerMonitor\Exceptions\InvalidCheckDefinition;
use Symfony\Component\Process\Process;

class Check extends Model
{
    public $casts = [
        'properties' => 'array',
    ];

    public $dates = [
        'checked_at', 'next_check_at',
    ];

    public function getAttribute($key)
    {
        return Arr::get($this->properties, $key, parent::getAttribute($key));
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
        if ($this->ssh_user) {
            return "{$this->ssh_user}&{$this->host}";
        }

        return $this->host;
    }

    protected function getDefinition(): CheckDefinition
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
        $delimiter = 'EOF-LARAVEL-SERVER-MONITOR';

        $definition = $this->getDefinition();

        return new Process(
            "ssh {$this->getTarget()} 'bash -se' << \\$delimiter".PHP_EOL
            .'set -e'.PHP_EOL
            .$definition->getCommand().PHP_EOL
            .$delimiter
        );
    }
}