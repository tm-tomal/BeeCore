<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsTemplate extends Model
{
    protected $fillable = ['key', 'name', 'content', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
