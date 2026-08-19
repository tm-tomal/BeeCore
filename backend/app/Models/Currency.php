<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    protected $fillable = ['code', 'name', 'symbol', 'decimal_places', 'exchange_rate', 'is_default', 'is_active'];

    protected $casts = [
        'decimal_places' => 'integer',
        'exchange_rate' => 'decimal:6',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function rateHistory(): HasMany
    {
        return $this->hasMany(CurrencyRateHistory::class);
    }

    public function format(float $amount): string
    {
        return $this->symbol.number_format($amount, $this->decimal_places);
    }
}
