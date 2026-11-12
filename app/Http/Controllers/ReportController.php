<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Farm;
use App\Models\Inventory;
use App\Models\SaleSummary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** FR-11 .. FR-14: harvest, inventory, sales and expense reports (printable). */
class ReportController extends Controller
{
    public const TYPES = ['harvest' => 'Harvest Report', 'inventory' => 'Inventory Report', 'sales' => 'Sales Report', 'expenses' => 'Expense Report'];

    public function show(Request $request, string $type)
    {
        abort_unless(isset(self::TYPES[$type]), 404);

        $from = $request->from;
        $to = $request->to;
        $farmId = $request->farm_id;
        $farm = $farmId ? Farm::find($farmId) : null;
        $data = $this->{$type}($from, $to, $farm);

        return view('reports.show', array_merge($data, [
            'type' => $type, 'title' => self::TYPES[$type], 'from' => $from, 'to' => $to, 'farm' => $farm,
            'farms' => Farm::orderBy('farm_name')->get(),
        ]));
    }

    private function harvest($from, $to, $farm): array
    {
        $rows = DB::table('harvests AS h')
            ->join('farms AS f', 'f.farm_id', '=', 'h.farm_id')
            ->join('rice_varieties AS rv', 'rv.variety_id', '=', 'h.variety_id')
            ->when($from, fn ($q) => $q->whereDate('h.harvest_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('h.harvest_date', '<=', $to))
            ->when($farm, fn ($q) => $q->where('h.farm_id', $farm->farm_id))
            ->selectRaw('f.farm_name, rv.variety_name, h.unit, COUNT(*) AS lots, SUM(h.harvested_quantity) AS qty')
            ->groupBy('f.farm_name', 'rv.variety_name', 'h.unit')->orderBy('f.farm_name')->orderBy('rv.variety_name')->get();

        return ['rows' => $rows, 'unitTotals' => $rows->groupBy('unit')->map(fn ($g) => $g->sum('qty'))];
    }

    private function inventory($from, $to, $farm): array
    {
        $q = Inventory::query()
            ->when($from, fn ($q) => $q->whereDate('harvest_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('harvest_date', '<=', $to))
            ->when($farm, fn ($q) => $q->where('farm_name', $farm->farm_name));
        $rows = $q->orderBy('farm_name')->orderBy('harvest_date')->get();

        return ['rows' => $rows, 'unitTotals' => $rows->groupBy('unit')->map(fn ($g) => [
            'harvested' => $g->sum('harvested_quantity'), 'sold' => $g->sum('quantity_sold'), 'available' => $g->sum('quantity_available'),
        ])];
    }

    private function sales($from, $to, $farm): array
    {
        $rows = SaleSummary::query()
            ->when($from, fn ($q) => $q->whereDate('sale_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('sale_date', '<=', $to))
            ->when($farm, fn ($q) => $q->whereIn('sale_id', DB::table('sale_details AS sd')->join('harvests AS h', 'h.harvest_id', '=', 'sd.harvest_id')->where('h.farm_id', $farm->farm_id)->select('sd.sale_id')))
            ->orderByDesc('sale_date')->get();

        $byVariety = DB::table('sale_details AS sd')
            ->join('sales AS s', 's.sale_id', '=', 'sd.sale_id')
            ->join('harvests AS h', 'h.harvest_id', '=', 'sd.harvest_id')
            ->join('rice_varieties AS rv', 'rv.variety_id', '=', 'h.variety_id')
            ->when($from, fn ($q) => $q->whereDate('s.sale_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('s.sale_date', '<=', $to))
            ->when($farm, fn ($q) => $q->where('h.farm_id', $farm->farm_id))
            ->selectRaw('rv.variety_name, h.unit, SUM(sd.quantity_sold) AS qty, SUM(sd.quantity_sold * sd.unit_price) AS revenue')
            ->groupBy('rv.variety_name', 'h.unit')->orderByDesc('revenue')->get();

        return [
            'rows' => $rows, 'byVariety' => $byVariety,
            'totals' => ['total' => $rows->sum('total_amount'), 'paid' => $rows->sum('amount_paid'), 'balance' => $rows->sum('balance')],
        ];
    }

    private function expenses($from, $to, $farm): array
    {
        $base = Expense::query()
            ->when($from, fn ($q) => $q->whereDate('expenses.expense_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('expenses.expense_date', '<=', $to))
            ->when($farm, fn ($q) => $q->where('expenses.farm_id', $farm->farm_id));

        return [
            'byCategory' => (clone $base)->selectRaw('expense_category, COUNT(*) AS n, SUM(amount) AS total')->groupBy('expense_category')->orderByDesc('total')->get(),
            'byFarm'     => (clone $base)->join('farms', 'farms.farm_id', '=', 'expenses.farm_id')->selectRaw('farms.farm_name, SUM(expenses.amount) AS total')->groupBy('farms.farm_name')->orderByDesc('total')->get(),
            'rows'       => (clone $base)->with(['farm', 'user'])->orderByDesc('expense_date')->get(),
            'grand'      => (clone $base)->sum('amount'),
        ];
    }
}
