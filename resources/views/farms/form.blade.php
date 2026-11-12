@extends('layouts.app')
@section('title', $farm->exists ? 'Edit farm' : 'Add farm')
@section('content')
<div class="page-head"><div><div class="crumbs"><a href="{{ route('farms.index') }}">Farms</a> /</div><h1>{{ $farm->exists ? 'Edit farm' : 'Add farm' }}</h1></div></div>
<div class="card" style="max-width:720px"><form class="card-b" method="POST" action="{{ $farm->exists ? route('farms.update', $farm) : route('farms.store') }}">
  @csrf @if($farm->exists) @method('PUT') @endif
  <div class="form-grid">
    <x-field class="span2" name="farm_name" label="Farm name" :value="$farm->farm_name" required/>
    <x-field class="span2" name="location" label="Location" :value="$farm->location" required/>
    <x-field name="area_hectares" label="Area (hectares)" type="number" step="0.01" min="0.01" :value="$farm->area_hectares" required/>
  </div>
  <div class="form-actions"><button class="btn btn-primary">Save farm</button><a class="btn" href="{{ route('farms.index') }}">Cancel</a></div>
</form></div>
@endsection
