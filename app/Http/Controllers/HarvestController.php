<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Models\Harvest;
use App\Models\RiceVariety;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class HarvestController extends Controller
{
    public function index(Request $request)
    {
        $harvests = Harvest::query()
            ->with(['farm', 'variety', 'user', 'inventory'])
            ->when($request->farm_id, fn ($q, $v) => $q->where('farm_id', $v))
            ->when($request->variety_id, fn ($q, $v) => $q->where('variety_id', $v))
            ->when($request->from, fn ($q, $v) => $q->whereDate('harvest_date', '>=', $v))
            ->when($request->to, fn ($q, $v) => $q->whereDate('harvest_date', '<=', $v))
            ->orderByDesc('harvest_date')->orderByDesc('harvest_id')
            ->paginate(12)->withQueryString();

        return view('harvests.index', [
            'harvests' => $harvests,
            'farms' => Farm::orderBy('farm_name')->get(),
            'varieties' => RiceVariety::orderBy('variety_name')->get(),
        ]);
    }

    public function create()
    {
        return view('harvests.form', $this->formData(new Harvest(['harvest_date' => now()->toDateString(), 'unit' => 'kg'])));
    }

    public function edit(Harvest $harvest)
    {
        return view('harvests.form', $this->formData($harvest));
    }

    public function show(Harvest $harvest)
    {
        $harvest->load(['farm', 'variety', 'user', 'inventory', 'saleDetails.sale.customer']);
        return view('harvests.show', compact('harvest'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['user_id'] = $request->user()->user_id;   // the user who recorded it
        $harvest = Harvest::create($data);
        return redirect()->route('harvests.show', $harvest)->with('success', 'Harvest recorded. It is now available as an inventory lot.');
    }

    public function update(Request $request, Harvest $harvest)
    {
        $data = $this->validated($request);
        $sold = (float) $harvest->saleDetails()->sum('quantity_sold');

        // Inventory must never be negative, and the unit of the lot cannot change once sales exist.
        if ($sold > 0 && (float) $data['harvested_quantity'] < $sold) {
            throw ValidationException::withMessages(['harvested_quantity' => 'Harvested quantity cannot be lower than the ' . number_format($sold, 2) . " {$harvest->unit} already sold from this harvest."]);
        }
        if ($sold > 0 && $data['unit'] !== $harvest->unit) {
            throw ValidationException::withMessages(['unit' => 'The unit cannot be changed because sales already reference this harvest.']);
        }

        $harvest->update($data);
        return redirect()->route('harvests.show', $harvest)->with('success', 'Harvest updated.');
    }

    public function destroy(Harvest $harvest) { return $this->tryDelete($harvest, 'harvests.index', 'Harvest'); }

    private function formData(Harvest $harvest): array
    {
        return [
            'harvest' => $harvest,
            'farms' => Farm::orderBy('farm_name')->get(),
            'varieties' => RiceVariety::orderBy('variety_name')->get(),
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'farm_id'            => ['required', 'exists:farms,farm_id'],
            'variety_id'         => ['required', 'exists:rice_varieties,variety_id'],
            'harvest_date'       => ['required', 'date', 'before_or_equal:today'],
            'harvested_quantity' => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
            'unit'               => ['required', Rule::in(['kg', 'sack'])],
            'remarks'            => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
