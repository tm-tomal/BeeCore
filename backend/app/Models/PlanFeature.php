<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanFeature extends Model
{
    public $timestamps = false;

    protected $fillable = ['saas_plan_id', 'feature_id', 'is_enabled'];

    protected $casts = ['is_enabled' => 'boolean'];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SaasPlan::class, 'saas_plan_id');
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }
}
