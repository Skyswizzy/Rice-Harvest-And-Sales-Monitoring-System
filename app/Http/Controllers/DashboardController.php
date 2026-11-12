<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Inventory;
use App\Models\SaleSummary;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // All sales figures come from vw_sales_summary and all stock figures from vw_inventory.
        $sales = SaleSummary::query()
            ->selectRaw('COUNT(*) AS n, COALESCE(SUM(total_amount),0) AS total, COALESCE(SUM(amount_paid),0) AS paid, COALESCE(SUM(balance),0) AS balance')
            ->first();

        $expenseTotal = (float) Expense::sum('amount');

        // Quantities are kept per unit: kg and sack must never be added together.
        $stock = Inventory::query()
            ->selectRaw('unit, COUNT(*) AS lots, SUM(harvested_quantity) AS harvested, SUM(quantity_sold) AS sold, SUM(quantity_available) AS available')
            ->groupBy('unit')->get()->keyBy('unit');

        $status = SaleSummary::query()->selectRaw('payment_status, COUNT(*) AS c')->groupBy('payment_status')->pluck('c', 'payment_status');

        $monthlySales = SaleSummary::query()
            ->selectRaw("DATE_FORMAT(sale_date,'%Y-%m') AS m, SUM(total_amount) AS v")->groupBy('m')->pluck('v', 'm');
        $monthlyExp = Expense::query()
            ->selectRaw("DATE_FORMAT(expense_date,'%Y-%m') AS m, SUM(amount) AS v")->groupBy('m')->pluck('v', 'm');
        $months = $monthlySales->keys()->merge($monthlyExp->keys())->unique()->sort()->values()->take(-12);
        $chart = [
            'labels'   => $months->map(fn ($m) => date('M Y', strtotime($m . '-01')))->all(),
            'sales'    => $months->map(fn ($m) => round((float) ($monthlySales[$m] ?? 0), 2))->all(),
            'expenses' => $months->map(fn ($m) => round((float) ($monthlyExp[$m] ?? 0), 2))->all(),
            'status'   => ['labels' => $status->keys()->all(), 'data' => $status->values()->all()],
        ];

        $lowStock = Inventory::where('quantity_available', '>', 0)
            ->orderByRaw('quantity_available / harvested_quantity ASC')->limit(5)->get();

        $recentSales = SaleSummary::orderByDesc('sale_date')->limit(6)->get();

        $expenseByCategory = Expense::query()
            ->selectRaw('expense_category, SUM(amount) AS total')->groupBy('expense_category')->orderByDesc('total')->limit(6)->get();

        // Farm performance: revenue flows farms -> harvests -> sale_details; expenses flow farms -> expenses.
        $revenue = DB::table('sale_details AS sd')->join('harvests AS h', 'h.harvest_id', '=', 'sd.harvest_id')
            ->selectRaw('h.farm_id, SUM(sd.quantity_sold * sd.unit_price) AS revenue')->groupBy('h.farm_id')->pluck('revenue', 'farm_id');
        $spent = Expense::query()->selectRaw('farm_id, SUM(amount) AS total')->groupBy('farm_id')->pluck('total', 'farm_id');
        $farms = \App\Models\Farm::orderBy('farm_name')->get()->map(function ($f) use ($revenue, $spent) {
            $f->revenue = (float) ($revenue[$f->farm_id] ?? 0);
            $f->spent = (float) ($spent[$f->farm_id] ?? 0);
            return $f;
        });

        return view('dashboard', compact('sales', 'expenseTotal', 'stock', 'status', 'chart', 'lowStock', 'recentSales', 'expenseByCategory', 'farms'));
    }
}
