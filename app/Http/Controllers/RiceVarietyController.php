<?php

namespace App\Http\Controllers;

use App\Models\RiceVariety;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RiceVarietyController extends Controller
{
    public function index()
    {
        $varieties = RiceVariety::withCount('harvests')->orderBy('variety_name')->get();
        return view('varieties.index', compact('varieties'));
    }

    public function create() { return view('varieties.form', ['variety' => new RiceVariety]); }
    public function edit(RiceVariety $variety) { return view('varieties.form', compact('variety')); }

    public function store(Request $request)
    {
        RiceVariety::create($this->validated($request));
        return redirect()->route('varieties.index')->with('success', 'Rice variety added.');
    }

    public function update(Request $request, RiceVariety $variety)
    {
        $variety->update($this->validated($request, $variety));
        return redirect()->route('varieties.index')->with('success', 'Rice variety updated.');
    }

    public function destroy(RiceVariety $variety) { return $this->tryDelete($variety, 'varieties.index', 'Rice variety'); }

    private function validated(Request $request, ?RiceVariety $variety = null): array
    {
        return $request->validate([
            // Business rule: each rice variety must have a unique name.
            'variety_name' => ['required', 'string', 'max:100', Rule::unique('rice_varieties', 'variety_name')->ignore($variety?->variety_id, 'variety_id')],
            'description'  => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
