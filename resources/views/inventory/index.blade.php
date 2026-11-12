@extends('layouts.app')
@section('title', 'Inventory')
@section('content')
<div class="page-head">
  <div><h1>Inventory</h1><p>Read-only view of available rice per harvest lot: harvested quantity − quantity sold. Updated automatically whenever a sale is recorded.</p></div>
  <div class="actions"><a class="btn btn-primary" href="{{ route('sales.create') }}"><x-icon name="cart"/> New sale</a></div>
</div>
<div class="grid g3 mb">
  @forelse($summary as $s)
    <div class="card kpi"><div class="ico"><x-icon name="package"/></div><div class="lbl">Available · {{ $s->unit }}</div><div class="val">{{ number_format($s->available, 2) }}</div><div class="sub">of {{ number_format($s->harvested, 2) }} harvested · {{ number_format($s->sold, 2) }} sold</div></div>
  @empty <div class="card kpi"><div class="lbl">No stock data</div></div> @endforelse
</div>
<div class="card">
  <form class="filters" method="GET">
    <div><label>Farm</label><select name="farm"><option value="">All farms</option>@foreach($farms as $f)<option value="{{ $f->farm_name }}" @selected(request('farm') === $f->farm_name)>{{ $f->farm_name }}</option>@endforeach</select></div>
    <div><label>Variety</label><input name="q" value="{{ request('q') }}" placeholder="Search variety…"></div>
    <div><label>Stock</label><select name="stock"><option value="">All lots</option><option value="available" @selected(request('stock')==='available')>In stock</option><option value="soldout" @selected(request('stock')==='soldout')>Sold out</option></select></div>
    <div style="flex:none;display:flex;gap:8px"><button class="btn btn-primary">Filter</button><a class="btn" href="{{ route('inventory.index') }}">Reset</a></div>
  </form>
  <div class="table-wrap"><table class="t"><thead><tr><th>Harvest</th><th>Date</th><th>Farm</th><th>Variety</th><th class="num">Harvested</th><th class="num">Sold</th><th>Available</th></tr></thead><tbody>
  @forelse($lots as $l)
    <tr><td><a href="{{ route('harvests.show', $l->harvest_id) }}"><b>#{{ $l->harvest_id }}</b></a></td><td class="nowrap">{{ $l->harvest_date->format('M d, Y') }}</td><td>{{ $l->farm_name }}</td><td>{{ $l->variety_name }}</td>
      <td class="num">{{ number_format($l->harvested_quantity, 2) }} <span class="badge b-unit">{{ $l->unit }}</span></td><td class="num">{{ number_format($l->quantity_sold, 2) }}</td>
      <td style="min-width:170px">
        @if($l->quantity_available <= 0)<span class="badge b-gray">Sold out</span>
        @else<b class="mono">{{ number_format($l->quantity_available, 2) }} {{ $l->unit }}</b><div class="bar {{ $l->percent_left < 25 ? 'low' : '' }}" style="margin-top:5px"><i style="width:{{ $l->percent_left }}%"></i></div>@endif
      </td></tr>
  @empty <tr><td colspan="7" class="empty">No lots match your filters.</td></tr> @endforelse
  </tbody></table></div>
  {{ $lots->links('pagination.rice') }}
</div>
@endsection
