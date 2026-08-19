<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CurrencyRateHistory extends Model
{
    protected $table = 'currency_rate_history';

    public $timestamps = false;

    protected $fillable = ['currency_id', 'rate', 'recorded_by', 'recorded_at'];

    protected $casts = ['rate' => 'decimal:6', 'recorded_at' => 'datetime'];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
