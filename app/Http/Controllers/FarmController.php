<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FarmController extends Controller
{
    public function index()
    {
        $farms = Farm::query()->withCount('harvests')->withSum('expenses', 'amount')->orderBy('farm_name')->get();
        return view('farms.index', compact('farms'));
    }

    public function create() { return view('farms.form', ['farm' => new Farm]); }
    public function edit(Farm $farm) { return view('farms.form', compact('farm')); }

    public function store(Request $request)
    {
        Farm::create($this->validated($request));
        return redirect()->route('farms.index')->with('success', 'Farm added.');
    }

    public function update(Request $request, Farm $farm)
    {
        $farm->update($this->validated($request, $farm));
        return redirect()->route('farms.index')->with('success', 'Farm updated.');
    }

    public function destroy(Farm $farm) { return $this->tryDelete($farm, 'farms.index', 'Farm'); }

    private function validated(Request $request, ?Farm $farm = null): array
    {
        return $request->validate([
            'farm_name'     => ['required', 'string', 'max:100', Rule::unique('farms', 'farm_name')->ignore($farm?->farm_id, 'farm_id')],
            'location'      => ['required', 'string', 'max:255'],
            'area_hectares' => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
        ]);
    }
}
