<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Issue extends Model
{
    public const CATEGORY_CONNECTION = 'connection';

    public const CATEGORY_NETWORK = 'network';

    public const CATEGORY_SERVICE = 'service';

    public const CATEGORY_BILLING = 'billing';

    public const CATEGORY_OTHER = 'other';

    public const STATUS_NEW = 'new';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_RESOLVED = 'resolved';

    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'tenant_id', 'customer_id', 'created_by', 'reporter_name', 'reporter_phone',
        'subject', 'category', 'priority', 'status', 'source', 'description', 'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
