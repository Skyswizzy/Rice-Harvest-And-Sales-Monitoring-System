@extends('layouts.app')
@section('title', $title)
@section('content')
@php($peso = fn($n) => '₱' . number_format((float) $n, 2))
<div class="page-head">
  <div><h1>{{ $title }}</h1><p>{{ $from || $to ? 'Period: ' . ($from ?: 'start') . ' to ' . ($to ?: 'today') : 'All records' }}{{ $farm ? ' · ' . $farm->farm_name : ' · All farms' }}</p></div>
  <div class="actions no-print"><button class="btn" onclick="window.print()"><x-icon name="print"/> Print / Save PDF</button></div>
</div>
<div class="card mb no-print">
  <form class="filters" method="GET">
    <div><label>Farm</label><select name="farm_id"><option value="">All farms</option>@foreach($farms as $f)<option value="{{ $f->farm_id }}" @selected(request('farm_id') == $f->farm_id)>{{ $f->farm_name }}</option>@endforeach</select></div>
    <div><label>From</label><input type="date" name="from" value="{{ $from }}"></div><div><label>To</label><input type="date" name="to" value="{{ $to }}"></div>
    <div style="flex:none;display:flex;gap:8px"><button class="btn btn-primary">Apply</button><a class="btn" href="{{ route('reports.show', $type) }}">Reset</a></div>
  </form>
</div>

@if($type === 'harvest')
  <div class="grid g3 mb">@foreach($unitTotals as $u => $q)<div class="card kpi"><div class="lbl">Total harvested · {{ $u }}</div><div class="val">{{ number_format($q, 2) }}</div></div>@endforeach</div>
  <div class="card"><div class="table-wrap"><table class="t"><thead><tr><th>Farm</th><th>Variety</th><th>Unit</th><th class="num">Harvests</th><th class="num">Quantity</th></tr></thead><tbody>
    @forelse($rows as $r)<tr><td>{{ $r->farm_name }}</td><td>{{ $r->variety_name }}</td><td><span class="badge b-unit">{{ $r->unit }}</span></td><td class="num">{{ $r->lots }}</td><td class="num">{{ number_format($r->qty, 2) }}</td></tr>
    @empty <tr><td colspan="5" class="empty">No harvests in this period.</td></tr>@endforelse</tbody></table></div></div>

@elseif($type === 'inventory')
  <div class="grid g3 mb">@foreach($unitTotals as $u => $t)<div class="card kpi"><div class="lbl">Available · {{ $u }}</div><div class="val">{{ number_format($t['available'], 2) }}</div><div class="sub">{{ number_format($t['harvested'], 2) }} harvested · {{ number_format($t['sold'], 2) }} sold</div></div>@endforeach</div>
  <div class="card"><div class="table-wrap"><table class="t"><thead><tr><th>Harvest</th><th>Date</th><th>Farm</th><th>Variety</th><th>Unit</th><th class="num">Harvested</th><th class="num">Sold</th><th class="num">Available</th></tr></thead><tbody>
    @forelse($rows as $r)<tr><td>#{{ $r->harvest_id }}</td><td>{{ $r->harvest_date->format('M d, Y') }}</td><td>{{ $r->farm_name }}</td><td>{{ $r->variety_name }}</td><td><span class="badge b-unit">{{ $r->unit }}</span></td>
      <td class="num">{{ number_format($r->harvested_quantity, 2) }}</td><td class="num">{{ number_format($r->quantity_sold, 2) }}</td><td class="num"><b>{{ number_format($r->quantity_available, 2) }}</b></td></tr>
    @empty <tr><td colspan="8" class="empty">No inventory records.</td></tr>@endforelse</tbody></table></div></div>

@elseif($type === 'sales')
  <div class="grid g3 mb"><div class="card kpi"><div class="lbl">Total sales</div><div class="val">{{ $peso($totals['total']) }}</div><div class="sub">{{ $rows->count() }} transactions</div></div>
    <div class="card kpi gold"><div class="lbl">Collected</div><div class="val">{{ $peso($totals['paid']) }}</div></div><div class="card kpi red"><div class="lbl">Outstanding</div><div class="val">{{ $peso($totals['balance']) }}</div></div></div>
  <div class="grid g-main">
    <div class="card"><div class="card-h"><h2>Transactions</h2></div><div class="table-wrap"><table class="t"><thead><tr><th>Sale</th><th>Date</th><th>Customer</th><th class="num">Total</th><th class="num">Paid</th><th class="num">Balance</th><th>Status</th></tr></thead><tbody>
      @forelse($rows as $r)<tr><td>#{{ $r->sale_id }}</td><td>{{ $r->sale_date->format('M d, Y') }}</td><td>{{ $r->customer_name }}</td><td class="num">{{ $peso($r->total_amount) }}</td><td class="num">{{ $peso($r->amount_paid) }}</td><td class="num">{{ $peso($r->balance) }}</td><td><x-status :value="$r->payment_status"/></td></tr>
      @empty <tr><td colspan="7" class="empty">No sales in this period.</td></tr>@endforelse</tbody></table></div></div>
    <div class="card"><div class="card-h"><h2>Revenue by variety</h2></div><div class="table-wrap"><table class="t"><thead><tr><th>Variety</th><th class="num">Qty sold</th><th class="num">Revenue</th></tr></thead><tbody>
      @foreach($byVariety as $v)<tr><td>{{ $v->variety_name }}</td><td class="num">{{ number_format($v->qty, 2) }} {{ $v->unit }}</td><td class="num">{{ $peso($v->revenue) }}</td></tr>@endforeach</tbody></table></div></div>
  </div>

@else
  <div class="grid g-main mb">
    <div class="card kpi blue"><div class="lbl">Total expenses</div><div class="val">{{ $peso($grand) }}</div><div class="sub">{{ $rows->count() }} records</div></div><div></div></div>
  <div class="grid g2 mb">
    <div class="card"><div class="card-h"><h2>By category</h2></div><div class="table-wrap"><table class="t"><tbody>@foreach($byCategory as $c)<tr><td>{{ $c->expense_category }} <span class="muted">({{ $c->n }})</span></td><td class="num">{{ $peso($c->total) }}</td></tr>@endforeach</tbody></table></div></div>
    <div class="card"><div class="card-h"><h2>By farm</h2></div><div class="table-wrap"><table class="t"><tbody>@foreach($byFarm as $c)<tr><td>{{ $c->farm_name }}</td><td class="num">{{ $peso($c->total) }}</td></tr>@endforeach</tbody></table></div></div>
  </div>
  <div class="card"><div class="card-h"><h2>All expenses</h2></div><div class="table-wrap"><table class="t"><thead><tr><th>Date</th><th>Farm</th><th>Category</th><th>Description</th><th class="num">Amount</th></tr></thead><tbody>
    @forelse($rows as $e)<tr><td>{{ $e->expense_date->format('M d, Y') }}</td><td>{{ $e->farm->farm_name }}</td><td>{{ $e->expense_category }}</td><td class="muted">{{ $e->description }}</td><td class="num">{{ $peso($e->amount) }}</td></tr>
    @empty <tr><td colspan="5" class="empty">No expenses in this period.</td></tr>@endforelse</tbody></table></div></div>
@endif
@endsection
