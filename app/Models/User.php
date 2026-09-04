<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['tenant_id', 'name', 'email', 'password', 'role', 'status', 'language', 'timezone', 'two_factor_enabled', 'two_factor_secret', 'notification_preferences'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_TENANT_ADMIN = 'tenant_admin';
    public const ROLE_FINANCE = 'finance';
    public const ROLE_SUPPORT = 'support';
    public const ROLE_NETWORK_ENGINEER = 'network_engineer';
    public const ROLE_RESELLER = 'reseller';

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN && $this->tenant_id === null && $this->status === 'active';
    }

    /**
     * Human-facing role name. ISP tenants call the tenant_admin role "ISP
     * Admin" (BeeCore staff are "BeeCore Admins").
     */
    public static function roleLabel(string $role): string
    {
        return [
            self::ROLE_SUPER_ADMIN => 'BeeCore Admin',
            self::ROLE_TENANT_ADMIN => 'ISP Admin',
            self::ROLE_FINANCE => 'Finance',
            self::ROLE_SUPPORT => 'Support',
            self::ROLE_NETWORK_ENGINEER => 'Network Engineer',
            self::ROLE_RESELLER => 'Reseller',
        ][$role] ?? ucwords(str_replace('_', ' ', $role));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
            'two_factor_secret' => 'encrypted',
            'notification_preferences' => 'array',
        ];
    }
}
