<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NetworkIntegrationLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['network_integration_id', 'direction', 'message', 'metadata', 'created_at'];

    protected $casts = ['metadata' => 'array', 'created_at' => 'datetime'];

    public function integration(): BelongsTo
    {
        return $this->belongsTo(NetworkIntegration::class, 'network_integration_id');
    }
}
