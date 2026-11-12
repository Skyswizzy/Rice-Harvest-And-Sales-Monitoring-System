<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Harvest;
use App\Models\Inventory;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\SalePayment;
use App\Models\SaleSummary;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        // The list is read from vw_sales_summary: total, paid, balance and status are derived, not stored.
        $base = SaleSummary::query()
            ->when($request->q, fn ($q, $v) => $q->where('customer_name', 'like', "%$v%"))
            ->when($request->status, fn ($q, $v) => $q->where('payment_status', $v))
            ->when($request->from, fn ($q, $v) => $q->whereDate('sale_date', '>=', $v))
            ->when($request->to, fn ($q, $v) => $q->whereDate('sale_date', '<=', $v));

        $totals = (clone $base)->selectRaw('COALESCE(SUM(total_amount),0) AS total, COALESCE(SUM(amount_paid),0) AS paid, COALESCE(SUM(balance),0) AS balance')->first();
        $sales = $base->orderByDesc('sale_date')->orderByDesc('sale_id')->paginate(12)->withQueryString();

        return view('sales.index', compact('sales', 'totals'));
    }

    public function create(Request $request)
    {
        return view('sales.create', [
            'customers' => Customer::orderBy('customer_name')->get(),
            'lots' => $this->availableLots(),
            'selectedCustomer' => $request->customer_id,
        ]);
    }

    /** FR-06, FR-08, FR-09: one transaction writes the sale, its lines and an optional first payment. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id'                => ['required', 'exists:customers,customer_id'],
            'sale_date'                  => ['required', 'date'],
            'notes'                      => ['nullable', 'string', 'max:2000'],
            'items'                      => ['required', 'array', 'min:1'],
            'items.*.harvest_id'         => ['required', 'integer', 'exists:harvests,harvest_id', 'distinct'],
            'items.*.quantity_sold'      => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
            'items.*.unit_price'         => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
            'payment_amount'             => ['nullable', 'numeric', 'gt:0', 'max:9999999999.99'],
            'payment_method'             => ['nullable', 'required_with:payment_amount', Rule::in(SalePayment::METHODS)],
            'reference_number'           => ['nullable', 'string', 'max:100'],
        ], [
            'items.required' => 'A sale must contain at least one item.',
            'items.*.harvest_id.distinct' => 'The same harvest can only appear once in a sale.',
        ]);

        try {
            $sale = DB::transaction(function () use ($data, $request) {
                $sale = Sale::create([
                    'customer_id' => $data['customer_id'],
                    'user_id'     => $request->user()->user_id,
                    'sale_date'   => Carbon::parse($data['sale_date']),
                    'notes'       => $data['notes'] ?? null,
                ]);

                $total = 0;
                foreach (array_values($data['items']) as $i => $item) {
                    $this->assertStock((int) $item['harvest_id'], (float) $item['quantity_sold'], "items.$i.quantity_sold");
                    $sale->details()->create(array_intersect_key($item, array_flip(['harvest_id', 'quantity_sold', 'unit_price'])));
                    $total += round($item['quantity_sold'] * $item['unit_price'], 4);
                }

                if (!empty($data['payment_amount'])) {
                    if (round($data['payment_amount'], 4) > round($total, 4)) {
                        throw ValidationException::withMessages(['payment_amount' => 'The payment (' . number_format($data['payment_amount'], 2) . ') is higher than the sale total (' . number_format($total, 2) . ').']);
                    }
                    $sale->payments()->create([
                        'payment_date'     => Carbon::parse($data['sale_date']),
                        'amount'           => $data['payment_amount'],
                        'payment_method'   => $data['payment_method'],
                        'reference_number' => $data['reference_number'] ?? null,
                    ]);
                }
                return $sale;
            });
        } catch (QueryException $e) {
            // e.g. the stock-validation triggers from the milestone rejected the row.
            return back()->withInput()->with('error', 'The database rejected this sale: ' . ($e->errorInfo[2] ?? 'constraint violation'));
        }

        return redirect()->route('sales.show', $sale)->with('success', 'Sale recorded and inventory updated automatically.');
    }

    public function show(Sale $sale)
    {
        $sale->load(['customer', 'user', 'details.harvest.farm', 'details.harvest.variety', 'payments', 'summary']);
        $usedHarvests = $sale->details->pluck('harvest_id')->all();
        $lots = $this->availableLots()->reject(fn ($l) => in_array($l->harvest_id, $usedHarvests))->values();
        return view('sales.show', compact('sale', 'lots'));
    }

    public function edit(Sale $sale)
    {
        return view('sales.edit', ['sale' => $sale, 'customers' => Customer::orderBy('customer_name')->get()]);
    }

    /** Only the header (customer, date, notes) is editable; lines and payments have their own actions. */
    public function update(Request $request, Sale $sale)
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,customer_id'],
            'sale_date'   => ['required', 'date'],
            'notes'       => ['nullable', 'string', 'max:2000'],
        ]);
        $sale->update($data);
        return redirect()->route('sales.show', $sale)->with('success', 'Sale updated.');
    }

    /** Historical sales are retained: only a sale with no lines and no payments can be deleted. */
    public function destroy(Sale $sale) { return $this->tryDelete($sale, 'sales.index', 'Sale'); }

    // ---- Sale lines ------------------------------------------------------------------

    public function addLine(Request $request, Sale $sale)
    {
        $data = $request->validate([
            'harvest_id'    => ['required', 'exists:harvests,harvest_id', Rule::unique('sale_details', 'harvest_id')->where('sale_id', $sale->sale_id)],
            'quantity_sold' => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
            'unit_price'    => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
        ], ['harvest_id.unique' => 'This harvest is already in the sale.']);

        try {
            DB::transaction(function () use ($data, $sale) {
                $this->assertStock((int) $data['harvest_id'], (float) $data['quantity_sold'], 'quantity_sold');
                $sale->details()->create($data);
            });
        } catch (QueryException $e) {
            return back()->with('error', 'The database rejected this item: ' . ($e->errorInfo[2] ?? 'constraint violation'));
        }
        return back()->with('success', 'Item added. Inventory updated automatically.');
    }

    public function removeLine(Sale $sale, SaleDetail $detail)
    {
        abort_unless((int) $detail->sale_id === (int) $sale->sale_id, 404);
        $sale->load('summary', 'details');

        if ($sale->details->count() <= 1) {
            return back()->with('error', 'A sale must keep at least one item.');
        }
        $newTotal = round((float) $sale->summary->total_amount - $detail->subtotal, 4);
        if ($newTotal < round((float) $sale->summary->amount_paid, 4)) {
            return back()->with('error', 'Removing this item would make the total lower than the amount already paid. Remove or adjust the payments first.');
        }
        $detail->delete();   // stock returns to inventory automatically (derived).
        return back()->with('success', 'Item removed. The quantity is back in inventory.');
    }

    // ---- Payments --------------------------------------------------------------------

    public function addPayment(Request $request, Sale $sale)
    {
        $data = $request->validate([
            'payment_date'     => ['required', 'date'],
            'amount'           => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
            'payment_method'   => ['required', Rule::in(SalePayment::METHODS)],
            'reference_number' => ['nullable', 'string', 'max:100'],
        ]);

        $summary = SaleSummary::find($sale->sale_id);
        if ((float) $summary->total_amount <= 0) {
            return back()->with('error', 'This sale has no items yet, so it cannot receive payments.');
        }
        if (round($data['amount'], 4) > round((float) $summary->balance, 4)) {
            throw ValidationException::withMessages(['amount' => 'The payment exceeds the remaining balance of ' . number_format($summary->balance, 2) . '.']);
        }

        $data['payment_date'] = Carbon::parse($data['payment_date']);
        $sale->payments()->create($data);
        return back()->with('success', 'Payment recorded.');
    }

    public function removePayment(Sale $sale, SalePayment $payment)
    {
        abort_unless((int) $payment->sale_id === (int) $sale->sale_id, 404);
        $payment->delete();
        return back()->with('success', 'Payment removed.');
    }

    // ---- Helpers ---------------------------------------------------------------------

    private function availableLots()
    {
        return Inventory::where('quantity_available', '>', 0)->orderBy('harvest_date')->get();
    }

    /**
     * Business rule: quantity sold must not exceed the available inventory of the harvest.
     * The harvest row is locked so two simultaneous sales cannot both claim the same stock.
     */
    private function assertStock(int $harvestId, float $qty, string $field): void
    {
        $harvest = Harvest::lockForUpdate()->findOrFail($harvestId);
        $available = $harvest->availableQuantity();
        if (round($qty, 2) > $available) {
            throw ValidationException::withMessages([
                $field => "Only " . number_format($available, 2) . " {$harvest->unit} left in Harvest #{$harvest->harvest_id}.",
            ]);
        }
    }
}
