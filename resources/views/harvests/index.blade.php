@extends('layouts.app')
@section('title', 'Harvests')
@section('content')
<div class="page-head">
  <div><h1>Harvests</h1><p>Every harvest is also an inventory lot. Remaining stock is calculated from sales automatically.</p></div>
  <div class="actions"><a class="btn btn-primary" href="{{ route('harvests.create') }}"><x-icon name="plus"/> Record harvest</a></div>
</div>
<div class="card">
  <form class="filters" method="GET">
    <div><label>Farm</label><select name="farm_id"><option value="">All farms</option>@foreach($farms as $f)<option value="{{ $f->farm_id }}" @selected(request('farm_id') == $f->farm_id)>{{ $f->farm_name }}</option>@endforeach</select></div>
    <div><label>Variety</label><select name="variety_id"><option value="">All varieties</option>@foreach($varieties as $v)<option value="{{ $v->variety_id }}" @selected(request('variety_id') == $v->variety_id)>{{ $v->variety_name }}</option>@endforeach</select></div>
    <div><label>From</label><input type="date" name="from" value="{{ request('from') }}"></div>
    <div><label>To</label><input type="date" name="to" value="{{ request('to') }}"></div>
    <div style="flex:none;display:flex;gap:8px"><button class="btn btn-primary">Filter</button><a class="btn" href="{{ route('harvests.index') }}">Reset</a></div>
  </form>
  <div class="table-wrap"><table class="t">
    <thead><tr><th>ID</th><th>Date</th><th>Farm</th><th>Variety</th><th class="num">Harvested</th><th class="num">Sold</th><th>Available</th><th>Recorded by</th><th></th></tr></thead><tbody>
    @forelse($harvests as $h)
      @php($inv = $h->inventory)
      <tr>
        <td><a href="{{ route('harvests.show', $h) }}"><b>#{{ $h->harvest_id }}</b></a></td>
        <td class="nowrap">{{ $h->harvest_date->format('M d, Y') }}</td><td>{{ $h->farm->farm_name }}</td><td>{{ $h->variety->variety_name }}</td>
        <td class="num">{{ number_format($h->harvested_quantity, 2) }} <span class="badge b-unit">{{ $h->unit }}</span></td>
        <td class="num">{{ number_format($inv->quantity_sold ?? 0, 2) }}</td>
        <td style="min-width:150px"><b class="mono">{{ number_format($inv->quantity_available ?? 0, 2) }}</b>
          <div class="bar {{ ($inv->percent_left ?? 0) <= 0 ? 'out' : (($inv->percent_left ?? 0) < 25 ? 'low' : '') }}" style="margin-top:5px"><i style="width:{{ $inv->percent_left ?? 0 }}%"></i></div></td>
        <td class="muted">{{ $h->user->full_name }}</td>
        <td><div class="row-actions"><a class="btn btn-sm" href="{{ route('harvests.show', $h) }}"><x-icon name="eye"/></a><a class="btn btn-sm" href="{{ route('harvests.edit', $h) }}"><x-icon name="edit"/></a></div></td>
      </tr>
    @empty <tr><td colspan="9" class="empty">No harvests match your filters.</td></tr> @endforelse
    </tbody></table></div>
  {{ $harvests->links('pagination.rice') }}
</div>
@endsection
