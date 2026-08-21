<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiClientLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['api_client_id', 'endpoint', 'method', 'status_code', 'is_failed', 'created_at'];

    protected $casts = ['is_failed' => 'boolean', 'created_at' => 'datetime'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(ApiClient::class, 'api_client_id');
    }
}
