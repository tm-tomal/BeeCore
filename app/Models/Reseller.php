<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reseller extends Model {
    protected $fillable = ['tenant_id', 'name', 'email', 'phone', 'balance', 'status'];
    protected $casts = ['balance' => 'decimal:2'];
    public function tenant() { return $this->belongsTo(Tenant::class); }
}
