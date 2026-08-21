<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailProvider extends Model
{
    protected $fillable = [
        'name', 'slug', 'type', 'provider', 'from_address', 'from_name',
        'is_active', 'credentials', 'archived_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'credentials' => 'encrypted:array',
        'archived_at' => 'datetime',
    ];

    protected $hidden = ['credentials'];

    public function logs(): HasMany
    {
        return $this->hasMany(EmailLog::class);
    }
}
