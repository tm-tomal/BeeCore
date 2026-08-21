<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['tenant_id', 'sms_provider_id', 'recipient', 'message', 'status', 'cost', 'created_at'];

    protected $casts = ['cost' => 'decimal:4', 'created_at' => 'datetime'];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(SmsProvider::class, 'sms_provider_id');
    }
}
