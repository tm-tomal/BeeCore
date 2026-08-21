<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['webhook_id', 'status_code', 'success', 'response_body', 'created_at'];

    protected $casts = ['success' => 'boolean', 'created_at' => 'datetime'];

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }
}
