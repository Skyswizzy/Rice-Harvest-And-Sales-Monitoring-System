<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::query()
            ->withCount('sales')
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('customer_name', 'like', "%$s%")->orWhere('contact_number', 'like', "%$s%")))
            ->orderBy('customer_name')->paginate(12)->withQueryString();
        return view('customers.index', compact('customers'));
    }

    public function create() { return view('customers.form', ['customer' => new Customer]); }
    public function edit(Customer $customer) { return view('customers.form', compact('customer')); }

    public function show(Customer $customer)
    {
        // Purchase history: sales header + derived totals from vw_sales_summary (via Sale::summary).
        $sales = $customer->sales()->with('summary')->orderByDesc('sale_date')->get();
        $totals = [
            'total'   => $sales->sum(fn ($s) => (float) ($s->summary->total_amount ?? 0)),
            'paid'    => $sales->sum(fn ($s) => (float) ($s->summary->amount_paid ?? 0)),
            'balance' => $sales->sum(fn ($s) => (float) ($s->summary->balance ?? 0)),
        ];
        return view('customers.show', compact('customer', 'sales', 'totals'));
    }

    public function store(Request $request)
    {
        $customer = Customer::create($this->validated($request));
        return redirect()->route('customers.show', $customer)->with('success', 'Customer registered.');
    }

    public function update(Request $request, Customer $customer)
    {
        $customer->update($this->validated($request, $customer));
        return redirect()->route('customers.show', $customer)->with('success', 'Customer updated.');
    }

    public function destroy(Customer $customer) { return $this->tryDelete($customer, 'customers.index', 'Customer'); }

    private function validated(Request $request, ?Customer $customer = null): array
    {
        $request->merge(['customer_name' => trim((string) $request->customer_name)]);
        return $request->validate([
            // Avoid duplicate customer records: same name + same address is treated as the same buyer.
            'customer_name'  => ['required', 'string', 'max:100',
                Rule::unique('customers', 'customer_name')->where(fn ($q) => $q->where('address', $request->address))->ignore($customer?->customer_id, 'customer_id')],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'address'        => ['nullable', 'string', 'max:255'],
        ], ['customer_name.unique' => 'A customer with this name and address already exists.']);
    }
}
