<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Models\Inventory;
use Illuminate\Http\Request;

/** FR-05 / FR-12. Read-only on purpose: inventory is derived from harvests and sale_details. */
class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $base = Inventory::query()
            ->when($request->farm, fn ($q, $v) => $q->where('farm_name', $v))
            ->when($request->q, fn ($q, $v) => $q->where('variety_name', 'like', "%$v%"))
            ->when($request->stock === 'available', fn ($q) => $q->where('quantity_available', '>', 0))
            ->when($request->stock === 'soldout', fn ($q) => $q->where('quantity_available', '<=', 0));

        $summary = (clone $base)
            ->selectRaw('unit, SUM(harvested_quantity) AS harvested, SUM(quantity_sold) AS sold, SUM(quantity_available) AS available')
            ->groupBy('unit')->get();

        $lots = $base->orderByDesc('harvest_date')->orderByDesc('harvest_id')->paginate(15)->withQueryString();

        return view('inventory.index', ['lots' => $lots, 'summary' => $summary, 'farms' => Farm::orderBy('farm_name')->get()]);
    }
}
