<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Read-only model over the vw_inventory view (available stock per harvest). */
class Inventory extends Model
{
    use ReadOnlyView;

    protected $table = 'vw_inventory';
    protected $primaryKey = 'harvest_id';
    public $timestamps = false;

    protected function casts(): array
    {
        return ['harvest_date' => 'date'];
    }

    public function harvest() { return $this->belongsTo(Harvest::class, 'harvest_id', 'harvest_id'); }

    public function getPercentLeftAttribute(): float
    {
        $h = (float) $this->harvested_quantity;
        return $h > 0 ? round(max(0, (float) $this->quantity_available) / $h * 100, 1) : 0;
    }
}
