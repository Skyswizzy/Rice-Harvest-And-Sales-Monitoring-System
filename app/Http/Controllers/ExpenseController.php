<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Farm;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $base = Expense::query()
            ->when($request->farm_id, fn ($q, $v) => $q->where('farm_id', $v))
            ->when($request->category, fn ($q, $v) => $q->where('expense_category', $v))
            ->when($request->from, fn ($q, $v) => $q->whereDate('expense_date', '>=', $v))
            ->when($request->to, fn ($q, $v) => $q->whereDate('expense_date', '<=', $v));

        $total = (clone $base)->sum('amount');
        $expenses = $base->with(['farm', 'user'])->orderByDesc('expense_date')->orderByDesc('expense_id')->paginate(12)->withQueryString();

        return view('expenses.index', ['expenses' => $expenses, 'total' => $total, 'farms' => Farm::orderBy('farm_name')->get(), 'categories' => Expense::categories()]);
    }

    public function create()
    {
        return view('expenses.form', $this->formData(new Expense(['expense_date' => now()->toDateString()])));
    }

    public function edit(Expense $expense) { return view('expenses.form', $this->formData($expense)); }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['user_id'] = $request->user()->user_id;
        Expense::create($data);
        return redirect()->route('expenses.index')->with('success', 'Expense recorded.');
    }

    public function update(Request $request, Expense $expense)
    {
        $expense->update($this->validated($request));
        return redirect()->route('expenses.index')->with('success', 'Expense updated.');
    }

    public function destroy(Expense $expense) { return $this->tryDelete($expense, 'expenses.index', 'Expense'); }

    private function formData(Expense $expense): array
    {
        return ['expense' => $expense, 'farms' => Farm::orderBy('farm_name')->get(), 'categories' => Expense::categories()];
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'farm_id'          => ['required', 'exists:farms,farm_id'],
            'expense_category' => ['required', 'string', 'max:100'],
            'description'      => ['nullable', 'string', 'max:2000'],
            'expense_date'     => ['required', 'date', 'before_or_equal:today'],
            'amount'           => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
        ]);
        $data['expense_category'] = Expense::canonicalCategory($data['expense_category']);
        return $data;
    }
}
