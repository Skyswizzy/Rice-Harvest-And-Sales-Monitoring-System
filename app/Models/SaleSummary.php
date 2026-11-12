<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Read-only model over vw_sales_summary (total, paid, balance, payment status per sale). */
class SaleSummary extends Model
{
    use ReadOnlyView;

    public const STATUSES = ['Paid', 'Partially Paid', 'Unpaid', 'No Items'];

    protected $table = 'vw_sales_summary';
    protected $primaryKey = 'sale_id';
    public $timestamps = false;

    protected function casts(): array
    {
        return ['sale_date' => 'datetime'];
    }

    public function sale() { return $this->belongsTo(Sale::class, 'sale_id', 'sale_id'); }
}
