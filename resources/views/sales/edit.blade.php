@extends('layouts.app')
@section('title', 'Edit sale #' . $sale->sale_id)
@section('content')
<div class="page-head"><div><div class="crumbs"><a href="{{ route('sales.show', $sale) }}">Sale #{{ $sale->sale_id }}</a> /</div><h1>Edit sale details</h1><p>Items and payments are managed on the sale page so stock and balances always stay consistent.</p></div></div>
<div class="card" style="max-width:720px"><form class="card-b" method="POST" action="{{ route('sales.update', $sale) }}">@csrf @method('PUT')
  <x-field name="customer_id" label="Customer" type="select" required>@foreach($customers as $c)<option value="{{ $c->customer_id }}" @selected(old('customer_id', $sale->customer_id) == $c->customer_id)>{{ $c->customer_name }}</option>@endforeach</x-field>
  <x-field name="sale_date" label="Date & time" type="datetime-local" :value="$sale->sale_date->format('Y-m-d\TH:i')" required/>
  <x-field name="notes" label="Notes" type="textarea" :value="$sale->notes"/>
  <div class="form-actions"><button class="btn btn-primary">Save</button><a class="btn" href="{{ route('sales.show', $sale) }}">Cancel</a></div>
</form></div>
@endsection
