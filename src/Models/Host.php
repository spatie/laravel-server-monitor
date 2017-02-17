<?php

namespace Spatie\ServerMonitor\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Host extends Model {

    public $guarded = [];

    public function checks(): HasMany
    {
        return $this->hasMany(Check::class);
    }
}