@extends('layouts.app')
@section('title', $variety->exists ? 'Edit variety' : 'Add variety')
@section('content')
<div class="page-head"><div><div class="crumbs"><a href="{{ route('varieties.index') }}">Rice varieties</a> /</div><h1>{{ $variety->exists ? 'Edit variety' : 'Add variety' }}</h1></div></div>
<div class="card" style="max-width:720px"><form class="card-b" method="POST" action="{{ $variety->exists ? route('varieties.update', $variety) : route('varieties.store') }}">
  @csrf @if($variety->exists) @method('PUT') @endif
  <x-field name="variety_name" label="Variety name" :value="$variety->variety_name" required hint="Must be unique."/>
  <x-field name="description" label="Description" type="textarea" :value="$variety->description"/>
  <div class="form-actions"><button class="btn btn-primary">Save variety</button><a class="btn" href="{{ route('varieties.index') }}">Cancel</a></div>
</form></div>
@endsection
