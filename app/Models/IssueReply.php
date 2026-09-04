<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssueReply extends Model
{
    public $timestamps = false;

    protected $fillable = ['issue_id', 'user_id', 'message', 'created_at'];

    protected $casts = ['created_at' => 'datetime'];

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
