@extends('layouts.app')
@section('title', 'Sales')
@section('content')
<div class="page-head">
  <div><h1>Sales</h1><p>Sales transactions with automatic totals, balances and payment status.</p></div>
  <div class="actions"><a class="btn btn-primary" href="{{ route('sales.create') }}"><x-icon name="plus"/> New sale</a></div>
</div>
<div class="grid g3 mb">
  <div class="card kpi"><div class="lbl">Total (filtered)</div><div class="val">₱{{ number_format($totals->total, 2) }}</div></div>
  <div class="card kpi gold"><div class="lbl">Collected</div><div class="val">₱{{ number_format($totals->paid, 2) }}</div></div>
  <div class="card kpi red"><div class="lbl">Balance</div><div class="val">₱{{ number_format($totals->balance, 2) }}</div></div>
</div>
<div class="card">
  <form class="filters" method="GET">
    <div><label>Customer</label><input name="q" value="{{ request('q') }}" placeholder="Search…"></div>
    <div><label>Status</label><select name="status"><option value="">All</option>@foreach(\App\Models\SaleSummary::STATUSES as $s)<option @selected(request('status') === $s)>{{ $s }}</option>@endforeach</select></div>
    <div><label>From</label><input type="date" name="from" value="{{ request('from') }}"></div><div><label>To</label><input type="date" name="to" value="{{ request('to') }}"></div>
    <div style="flex:none;display:flex;gap:8px"><button class="btn btn-primary">Filter</button><a class="btn" href="{{ route('sales.index') }}">Reset</a></div>
  </form>
  <div class="table-wrap"><table class="t"><thead><tr><th>Sale</th><th>Date</th><th>Customer</th><th>Recorded by</th><th class="num">Total</th><th class="num">Paid</th><th class="num">Balance</th><th>Status</th></tr></thead><tbody>
  @forelse($sales as $s)
    <tr><td><a href="{{ route('sales.show', $s->sale_id) }}"><b>#{{ $s->sale_id }}</b></a></td><td class="nowrap">{{ $s->sale_date->format('M d, Y g:i A') }}</td><td>{{ $s->customer_name }}</td><td class="muted">{{ $s->recorded_by }}</td>
      <td class="num">₱{{ number_format($s->total_amount, 2) }}</td><td class="num">₱{{ number_format($s->amount_paid, 2) }}</td><td class="num">₱{{ number_format($s->balance, 2) }}</td><td><x-status :value="$s->payment_status"/></td></tr>
  @empty <tr><td colspan="8" class="empty">No sales found.</td></tr> @endforelse
  </tbody></table></div>
  {{ $sales->links('pagination.rice') }}
</div>
@endsection
