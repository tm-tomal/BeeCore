<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    protected $fillable = [
        'title', 'body', 'type', 'tenant_id', 'status',
        'dashboard_channel', 'email_channel', 'sms_channel', 'push_channel',
        'publish_at', 'published_at', 'created_by',
    ];

    protected $casts = [
        'dashboard_channel' => 'boolean',
        'email_channel' => 'boolean',
        'sms_channel' => 'boolean',
        'push_channel' => 'boolean',
        'publish_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActiveFor($query, ?int $tenantId)
    {
        return $query->where('status', 'published')
            ->where('dashboard_channel', true)
            ->where(fn ($q) => $q->whereNull('tenant_id')->orWhere('tenant_id', $tenantId));
    }
}
