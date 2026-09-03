<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaServer extends Model
{
    protected $fillable = ['name', 'host', 'status', 'storage_capacity_gb', 'last_checked_at'];

    protected $casts = ['last_checked_at' => 'datetime'];
}
