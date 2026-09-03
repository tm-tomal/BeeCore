<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Network extends Model {
    protected $fillable = ['tenant_id', 'name', 'ip_address', 'device_type', 'location', 'status'];
    public function tenant() { return $this->belongsTo(Tenant::class); }
}
