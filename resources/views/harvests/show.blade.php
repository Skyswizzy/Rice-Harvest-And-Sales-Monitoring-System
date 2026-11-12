@extends('layouts.app')
@section('title', 'Harvest #' . $harvest->harvest_id)
@section('content')
@php($inv = $harvest->inventory)
<div class="page-head">
  <div><div class="crumbs"><a href="{{ route('harvests.index') }}">Harvests</a> /</div><h1>Harvest #{{ $harvest->harvest_id }} · {{ $harvest->variety->variety_name }}</h1><p>{{ $harvest->farm->farm_name }} · {{ $harvest->harvest_date->format('F d, Y') }}</p></div>
  <div class="actions"><a class="btn" href="{{ route('harvests.edit', $harvest) }}"><x-icon name="edit"/> Edit</a>
    @if(auth()->user()->isAdmin())<form method="POST" action="{{ route('harvests.destroy', $harvest) }}" data-confirm="Delete this harvest?">@csrf @method('DELETE')<button class="btn btn-danger"><x-icon name="trash"/> Delete</button></form>@endif</div>
</div>
<div class="grid g-main">
  <div class="card">
    <div class="card-h"><h2>Sales from this harvest</h2></div>
    <div class="table-wrap"><table class="t"><thead><tr><th>Sale</th><th>Date</th><th>Customer</th><th class="num">Quantity</th><th class="num">Unit price</th><th class="num">Subtotal</th></tr></thead><tbody>
      @forelse($harvest->saleDetails as $d)
        <tr><td><a href="{{ route('sales.show', $d->sale_id) }}"><b>#{{ $d->sale_id }}</b></a></td><td>{{ $d->sale->sale_date->format('M d, Y') }}</td><td>{{ $d->sale->customer->customer_name }}</td>
          <td class="num">{{ number_format($d->quantity_sold, 2) }} {{ $harvest->unit }}</td><td class="num">₱{{ number_format($d->unit_price, 2) }}</td><td class="num">₱{{ number_format($d->subtotal, 2) }}</td></tr>
      @empty <tr><td colspan="6" class="empty">Nothing sold from this harvest yet.</td></tr> @endforelse
    </tbody></table></div>
  </div>
  <div class="card"><div class="card-h"><h2>Inventory lot</h2></div><div class="card-b">
    <div class="statline mb"><div><span>Available</span><b>{{ number_format($inv->quantity_available ?? 0, 2) }} {{ $harvest->unit }}</b></div></div>
    <div class="bar mb"><i style="width:{{ $inv->percent_left ?? 0 }}%"></i></div>
    <dl class="dl">
      <dt>Harvested</dt><dd>{{ number_format($harvest->harvested_quantity, 2) }} {{ $harvest->unit }}</dd>
      <dt>Sold</dt><dd>{{ number_format($inv->quantity_sold ?? 0, 2) }} {{ $harvest->unit }}</dd>
      <dt>Farm</dt><dd>{{ $harvest->farm->farm_name }}</dd><dt>Recorded by</dt><dd>{{ $harvest->user->full_name }}</dd>
      <dt>Remarks</dt><dd>{{ $harvest->remarks ?: '—' }}</dd>
    </dl>
  </div></div>
</div>
@endsection
