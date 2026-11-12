@extends('layouts.app')
@section('title', 'Sale #' . $sale->sale_id)
@section('content')
@php($sum = $sale->summary)
@php($admin = auth()->user()->isAdmin())
<div class="page-head">
  <div><div class="crumbs"><a href="{{ route('sales.index') }}">Sales</a> /</div><h1>Sale #{{ $sale->sale_id }} <x-status :value="$sum->payment_status"/></h1>
    <p>{{ $sale->sale_date->format('F d, Y g:i A') }} · recorded by {{ $sale->user->full_name }}</p></div>
  <div class="actions"><button class="btn no-print" onclick="window.print()"><x-icon name="print"/> Print</button><a class="btn" href="{{ route('sales.edit', $sale) }}"><x-icon name="edit"/> Edit details</a>
    @if($admin)<form method="POST" action="{{ route('sales.destroy', $sale) }}" data-confirm="Delete this sale?">@csrf @method('DELETE')<button class="btn btn-danger"><x-icon name="trash"/></button></form>@endif</div>
</div>
<div class="card mb"><div class="card-b statline">
  <div><span>Customer</span><b style="font-size:18px"><a href="{{ route('customers.show', $sale->customer) }}">{{ $sale->customer->customer_name }}</a></b></div>
  <div><span>Total</span><b>₱{{ number_format($sum->total_amount, 2) }}</b></div><div><span>Paid</span><b>₱{{ number_format($sum->amount_paid, 2) }}</b></div>
  <div><span>Balance</span><b style="color:{{ $sum->balance > 0 ? 'var(--danger)' : 'var(--ok)' }}">₱{{ number_format($sum->balance, 2) }}</b></div>
</div>@if($sale->notes)<div class="card-b" style="border-top:1px solid var(--line)"><span class="muted">Notes:</span> {{ $sale->notes }}</div>@endif</div>

<div class="grid g-main">
  <div>
    <div class="card mb"><div class="card-h"><h2>Items</h2></div><div class="table-wrap"><table class="t">
      <thead><tr><th>Harvest lot</th><th class="num">Quantity</th><th class="num">Unit price</th><th class="num">Subtotal</th>@if($admin)<th></th>@endif</tr></thead><tbody>
      @foreach($sale->details as $d)
        <tr><td><a href="{{ route('harvests.show', $d->harvest_id) }}"><b>#{{ $d->harvest_id }}</b></a> · {{ $d->harvest->variety->variety_name }}<br><span class="muted" style="font-size:12.5px">{{ $d->harvest->farm->farm_name }}</span></td>
          <td class="num">{{ number_format($d->quantity_sold, 2) }} {{ $d->harvest->unit }}</td><td class="num">₱{{ number_format($d->unit_price, 2) }}</td><td class="num">₱{{ number_format($d->subtotal, 2) }}</td>
          @if($admin)<td><form method="POST" action="{{ route('sales.lines.destroy', [$sale, $d]) }}" data-confirm="Remove this item? The quantity returns to inventory.">@csrf @method('DELETE')<button class="btn btn-sm btn-danger btn-icon"><x-icon name="trash"/></button></form></td>@endif</tr>
      @endforeach
      </tbody><tfoot><tr><td colspan="3" class="right">Total</td><td class="num">₱{{ number_format($sum->total_amount, 2) }}</td>@if($admin)<td></td>@endif</tr></tfoot></table></div>
      @if($lots->isNotEmpty())
      <form class="card-b inline" style="border-top:1px solid var(--line)" method="POST" action="{{ route('sales.lines.store', $sale) }}">@csrf
        <div class="field" style="margin:0"><label>Add another lot</label><select name="harvest_id" required><option value="">Select harvest…</option>@foreach($lots as $l)<option value="{{ $l->harvest_id }}">#{{ $l->harvest_id }} · {{ $l->variety_name }} · {{ number_format($l->quantity_available, 2) }} {{ $l->unit }} left</option>@endforeach</select></div>
        <div class="field" style="margin:0"><label>Quantity</label><input type="number" step="0.01" min="0.01" name="quantity_sold" required></div>
        <div class="field" style="margin:0"><label>Unit price</label><input type="number" step="0.01" min="0.01" name="unit_price" required></div>
        <button class="btn btn-primary">Add</button></form>
      @endif
    </div>
  </div>
  <div class="card"><div class="card-h"><h2>Payments</h2></div>
    @forelse($sale->payments as $p)
      <div class="list-row"><div><b class="mono">₱{{ number_format($p->amount, 2) }}</b> <span class="badge b-unit">{{ $p->payment_method }}</span><br><span class="muted" style="font-size:12.5px">{{ $p->payment_date->format('M d, Y g:i A') }}{{ $p->reference_number ? ' · ' . $p->reference_number : '' }}</span></div>
        @if($admin)<form method="POST" action="{{ route('sales.payments.destroy', [$sale, $p]) }}" data-confirm="Remove this payment?">@csrf @method('DELETE')<button class="btn btn-sm btn-danger btn-icon"><x-icon name="trash"/></button></form>@endif</div>
    @empty <div class="empty">No payments yet.</div> @endforelse
    @if($sum->balance > 0)
    <form class="card-b" style="border-top:1px solid var(--line)" method="POST" action="{{ route('sales.payments.store', $sale) }}">@csrf
      <x-field name="amount" label="Amount received" type="number" step="0.01" min="0.01" :max="$sum->balance" :value="$sum->balance" required :hint="'Remaining balance: ₱' . number_format($sum->balance, 2)"/>
      <x-field name="payment_date" label="Date & time" type="datetime-local" :value="now()->format('Y-m-d\TH:i')" required/>
      <x-field name="payment_method" label="Method" type="select" required>@foreach(\App\Models\SalePayment::METHODS as $m)<option>{{ $m }}</option>@endforeach</x-field>
      <x-field name="reference_number" label="Reference no."/>
      <button class="btn btn-gold btn-block">Record payment</button></form>
    @endif
  </div>
</div>
@endsection
