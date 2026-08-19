<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantBranding extends Model
{
    protected $fillable = [
        'tenant_id', 'is_enabled', 'brand_name', 'brand_color',
        'logo_path', 'favicon_path', 'app_name', 'app_icon_path', 'splash_screen_path',
        'login_branding_enabled', 'dashboard_branding_enabled', 'email_branding_enabled',
        'sms_branding_enabled', 'customer_app_branding_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'login_branding_enabled' => 'boolean',
        'dashboard_branding_enabled' => 'boolean',
        'email_branding_enabled' => 'boolean',
        'sms_branding_enabled' => 'boolean',
        'customer_app_branding_enabled' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
