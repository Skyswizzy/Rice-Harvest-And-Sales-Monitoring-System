@extends('layouts.app')
@section('title', $harvest->exists ? 'Edit harvest' : 'Record harvest')
@section('content')
<div class="page-head"><div><div class="crumbs"><a href="{{ route('harvests.index') }}">Harvests</a> /</div><h1>{{ $harvest->exists ? 'Edit harvest #' . $harvest->harvest_id : 'Record harvest' }}</h1>
  <p>No inventory field here on purpose — available stock is derived from this harvest minus its sales.</p></div></div>
<div class="card" style="max-width:820px"><form class="card-b" method="POST" action="{{ $harvest->exists ? route('harvests.update', $harvest) : route('harvests.store') }}">
  @csrf @if($harvest->exists) @method('PUT') @endif
  <div class="form-grid">
    <x-field name="farm_id" label="Farm" type="select" required>
      <option value="">Select farm…</option>
      @foreach($farms as $f)<option value="{{ $f->farm_id }}" @selected(old('farm_id', $harvest->farm_id) == $f->farm_id)>{{ $f->farm_name }}</option>@endforeach
    </x-field>
    <x-field name="variety_id" label="Rice variety" type="select" required>
      <option value="">Select variety…</option>
      @foreach($varieties as $v)<option value="{{ $v->variety_id }}" @selected(old('variety_id', $harvest->variety_id) == $v->variety_id)>{{ $v->variety_name }}</option>@endforeach
    </x-field>
    <x-field name="harvest_date" label="Harvest date" type="date" :value="optional($harvest->harvest_date)->format('Y-m-d') ?? $harvest->harvest_date" :max="now()->toDateString()" required/>
    <x-field name="harvested_quantity" label="Harvested quantity" type="number" step="0.01" min="0.01" :value="$harvest->harvested_quantity" required/>
    <x-field name="unit" label="Unit" type="select" required>
      @foreach(['kg' => 'Kilograms (kg)', 'sack' => 'Sacks'] as $k => $l)<option value="{{ $k }}" @selected(old('unit', $harvest->unit) === $k)>{{ $l }}</option>@endforeach
    </x-field>
    <x-field class="span2" name="remarks" label="Remarks" type="textarea" :value="$harvest->remarks"/>
  </div>
  <div class="form-actions"><button class="btn btn-primary">Save harvest</button><a class="btn" href="{{ route('harvests.index') }}">Cancel</a></div>
</form></div>
@endsection
