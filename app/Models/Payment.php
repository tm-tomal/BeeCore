<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model {
    protected $fillable = ['tenant_id', 'invoice_id', 'customer_id', 'amount', 'payment_method', 'transaction_id', 'payment_date', 'status'];
    protected $casts = ['payment_date' => 'datetime', 'amount' => 'decimal:2'];
    public function tenant() { return $this->belongsTo(Tenant::class); }
    public function invoice() { return $this->belongsTo(Invoice::class); }
    public function customer() { return $this->belongsTo(Customer::class); }
}
