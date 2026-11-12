<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $table = 'sales';
    protected $primaryKey = 'sale_id';
    public $timestamps = false;
    protected $fillable = ['customer_id', 'user_id', 'sale_date', 'notes'];

    protected function casts(): array
    {
        return ['sale_date' => 'datetime'];
    }

    public function customer() { return $this->belongsTo(Customer::class, 'customer_id', 'customer_id'); }
    public function user()     { return $this->belongsTo(User::class, 'user_id', 'user_id'); }
    public function details()  { return $this->hasMany(SaleDetail::class, 'sale_id', 'sale_id'); }
    public function payments() { return $this->hasMany(SalePayment::class, 'sale_id', 'sale_id'); }
    /** Totals, amount paid, balance and status come from vw_sales_summary, never stored. */
    public function summary()  { return $this->hasOne(SaleSummary::class, 'sale_id', 'sale_id'); }
}
