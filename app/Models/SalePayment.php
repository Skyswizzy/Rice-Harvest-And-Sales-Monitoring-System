<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalePayment extends Model
{
    protected $table = 'sale_payments';
    protected $primaryKey = 'payment_id';
    public $timestamps = false;
    protected $fillable = ['sale_id', 'payment_date', 'amount', 'payment_method', 'reference_number'];

    public const METHODS = ['Cash', 'Bank Transfer', 'E-Wallet', 'Other'];

    protected function casts(): array
    {
        return ['payment_date' => 'datetime'];
    }

    public function sale() { return $this->belongsTo(Sale::class, 'sale_id', 'sale_id'); }
}
