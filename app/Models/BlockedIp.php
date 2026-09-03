<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockedIp extends Model
{
    public $timestamps = false;

    protected $fillable = ['ip_address', 'reason', 'blocked_by', 'blocked_at'];

    protected $casts = ['blocked_at' => 'datetime'];

    public function blockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }
}
