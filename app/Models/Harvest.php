<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A harvest is also the inventory lot. Stock is NEVER stored:
 * it is read from the vw_inventory view (see inventory()) or computed on demand.
 */
class Harvest extends Model
{
    protected $table = 'harvests';
    protected $primaryKey = 'harvest_id';
    public $timestamps = false;
    protected $fillable = ['farm_id', 'variety_id', 'user_id', 'harvest_date', 'harvested_quantity', 'unit', 'remarks'];

    protected function casts(): array
    {
        return ['harvest_date' => 'date'];
    }

    public function farm()        { return $this->belongsTo(Farm::class, 'farm_id', 'farm_id'); }
    public function variety()     { return $this->belongsTo(RiceVariety::class, 'variety_id', 'variety_id'); }
    public function user()        { return $this->belongsTo(User::class, 'user_id', 'user_id'); }
    public function saleDetails() { return $this->hasMany(SaleDetail::class, 'harvest_id', 'harvest_id'); }
    public function inventory()   { return $this->hasOne(Inventory::class, 'harvest_id', 'harvest_id'); }

    /** harvested quantity - total sold (same formula as vw_inventory). */
    public function availableQuantity(): float
    {
        return round((float) $this->harvested_quantity - (float) $this->saleDetails()->sum('quantity_sold'), 2);
    }

    public function getLabelAttribute(): string
    {
        return "Harvest #{$this->harvest_id}";
    }
}
