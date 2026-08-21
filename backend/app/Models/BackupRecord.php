<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupRecord extends Model
{
    public $timestamps = false;

    protected $fillable = ['type', 'status', 'disk', 'path', 'size_bytes', 'triggered_by', 'started_at', 'completed_at'];

    protected $casts = ['started_at' => 'datetime', 'completed_at' => 'datetime'];

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}
