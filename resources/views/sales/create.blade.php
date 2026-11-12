@extends('layouts.app')
@section('title', 'New sale')
@section('content')
<div class="page-head"><div><div class="crumbs"><a href="{{ route('sales.index') }}">Sales</a> /</div><h1>New sale</h1>
  <p>Totals are calculated automatically (quantity × price). Stock is deducted from the selected harvest lots when you save.</p></div></div>

@if($lots->isEmpty())
  <div class="alert alert-info"><x-icon name="alert"/><span>There is no rice in stock. Record a harvest first.</span></div>
@endif

<form method="POST" action="{{ route('sales.store') }}">
@csrf
<div class="grid g-main">
  <div>
    <div class="card mb"><div class="card-h"><h2>Items sold</h2><button type="button" id="add-line" class="btn btn-sm"><x-icon name="plus"/> Add item</button></div>
      <div class="card-b">
        <div class="lines" id="lines">
          @foreach(old('items', [[]]) as $i => $item) @include('sales._row', ['i' => $i, 'item' => $item, 'lots' => $lots]) @endforeach
        </div>
        <div class="total-box"><span>Total amount</span><b id="grand-total">₱0.00</b></div>
        <template id="line-template">@include('sales._row', ['i' => '__i__', 'item' => [], 'lots' => $lots])</template>
      </div></div>
  </div>
  <div>
    <div class="card mb"><div class="card-h"><h2>Sale details</h2></div><div class="card-b">
      <x-field name="customer_id" label="Customer" type="select" required>
        <option value="">Select customer…</option>
        @foreach($customers as $c)<option value="{{ $c->customer_id }}" @selected(old('customer_id', $selectedCustomer) == $c->customer_id)>{{ $c->customer_name }}</option>@endforeach
      </x-field>
      <p class="hint" style="margin:-8px 0 14px"><a href="{{ route('customers.create') }}">Register a new customer</a></p>
      <x-field name="sale_date" label="Date & time" type="datetime-local" :value="now()->format('Y-m-d\TH:i')" required/>
      <x-field name="notes" label="Notes" type="textarea"/>
    </div></div>
    <div class="card mb"><div class="card-h"><h2>Initial payment <span class="muted" style="font-size:13px;font-weight:400">(optional)</span></h2></div><div class="card-b">
      <div class="field"><label for="payment_amount">Amount received</label><input id="payment_amount" name="payment_amount" type="number" step="0.01" min="0.01" value="{{ old('payment_amount') }}"><p class="hint">Balance after payment: <b id="balance-after">₱0.00</b></p>
        @error('payment_amount')<p class="err">{{ $message }}</p>@enderror</div>
      <x-field name="payment_method" label="Method" type="select">
        @foreach(\App\Models\SalePayment::METHODS as $m)<option @selected(old('payment_method', 'Cash') === $m)>{{ $m }}</option>@endforeach
      </x-field>
      <x-field name="reference_number" label="Reference no."/>
    </div></div>
    <button class="btn btn-primary btn-block" type="submit"><x-icon name="check"/> Save sale</button>
  </div>
</div>
</form>
@endsection
