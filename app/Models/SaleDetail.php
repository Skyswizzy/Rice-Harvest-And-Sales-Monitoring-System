<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleDetail extends Model
{
    protected $table = 'sale_details';
    protected $primaryKey = 'sale_detail_id';
    public $timestamps = false;
    protected $fillable = ['sale_id', 'harvest_id', 'quantity_sold', 'unit_price'];

    public function sale()    { return $this->belongsTo(Sale::class, 'sale_id', 'sale_id'); }
    public function harvest() { return $this->belongsTo(Harvest::class, 'harvest_id', 'harvest_id'); }

    /** Derived value (quantity x unit price). Not a column. */
    public function getSubtotalAttribute(): float
    {
        return round((float) $this->quantity_sold * (float) $this->unit_price, 4);
    }
}
