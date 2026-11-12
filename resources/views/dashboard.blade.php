@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
@php
  $peso = fn($n) => '₱' . number_format((float) $n, 2);
  $net = (float) $sales->paid - $expenseTotal;
@endphp

<section class="hero">
  <h1>Farm overview</h1>
  <p>Live totals from your harvests, inventory, sales and expenses. Stock and balances are calculated automatically from the recorded transactions.</p>
</section>

<div class="grid g4 mb">
  <div class="card kpi"><div class="ico"><x-icon name="cart"/></div><div class="lbl">Total sales</div><div class="val">{{ $peso($sales->total) }}</div><div class="sub">{{ $sales->n }} transactions</div></div>
  <div class="card kpi gold"><div class="ico"><x-icon name="peso"/></div><div class="lbl">Collected</div><div class="val">{{ $peso($sales->paid) }}</div><div class="sub">Payments received</div></div>
  <div class="card kpi red"><div class="ico"><x-icon name="alert"/></div><div class="lbl">Receivables</div><div class="val">{{ $peso($sales->balance) }}</div><div class="sub">Unpaid balance of all sales</div></div>
  <div class="card kpi blue"><div class="ico"><x-icon name="wallet"/></div><div class="lbl">Expenses</div><div class="val">{{ $peso($expenseTotal) }}</div><div class="sub">Net cash: <b style="color:{{ $net < 0 ? 'var(--danger)' : 'var(--ok)' }}">{{ $peso($net) }}</b> (collected − expenses)</div></div>
</div>

<div class="grid g-main mb">
  <div class="card">
    <div class="card-h"><h2>Sales vs. expenses by month</h2><span class="muted">Peso</span></div>
    <div class="chart-box"><canvas id="monthly"></canvas></div>
  </div>
  <div class="card">
    <div class="card-h"><h2>Stock on hand</h2><a href="{{ route('inventory.index') }}" class="btn btn-sm">Inventory</a></div>
    <div class="card-b">
      @forelse($stock as $unit => $s)
        @php($pct = $s->harvested > 0 ? max(0, $s->available) / $s->harvested * 100 : 0)
        <div style="margin-bottom:18px">
          <div style="display:flex;justify-content:space-between;align-items:baseline">
            <b class="mono" style="font-family:var(--serif);font-size:24px;color:var(--forest)">{{ number_format($s->available, 2) }} <small style="font-size:14px">{{ $unit }}</small></b>
            <span class="muted">{{ round($pct) }}% left</span>
          </div>
          <div class="bar {{ $pct < 25 ? 'low' : '' }}" style="margin:6px 0"><i style="width:{{ $pct }}%"></i></div>
          <div class="muted" style="font-size:13px">Harvested {{ number_format($s->harvested, 2) }} · Sold {{ number_format($s->sold, 2) }} · {{ $s->lots }} lots</div>
        </div>
      @empty
        <div class="empty">No harvests recorded yet.</div>
      @endforelse
      <p class="muted" style="font-size:12.5px;margin:0">Kilograms and sacks are tracked separately and never added together.</p>
    </div>
  </div>
</div>

<div class="grid g2 mb">
  <div class="card">
    <div class="card-h"><h2>Recent sales</h2><a href="{{ route('sales.index') }}" class="btn btn-sm">View all</a></div>
    <div class="table-wrap"><table class="t"><thead><tr><th>Sale</th><th>Customer</th><th class="num">Total</th><th>Status</th></tr></thead><tbody>
      @forelse($recentSales as $s)
        <tr><td><a href="{{ route('sales.show', $s->sale_id) }}"><b>#{{ $s->sale_id }}</b></a><br><span class="muted" style="font-size:12.5px">{{ $s->sale_date->format('M d, Y') }}</span></td>
          <td>{{ $s->customer_name }}</td><td class="num">{{ $peso($s->total_amount) }}</td><td><x-status :value="$s->payment_status"/></td></tr>
      @empty <tr><td colspan="4" class="empty">No sales yet.</td></tr> @endforelse
    </tbody></table></div>
  </div>
  <div class="card">
    <div class="card-h"><h2>Lots running low</h2><span class="muted">Lowest % remaining</span></div>
    @forelse($lowStock as $l)
      <div class="list-row">
        <div><a href="{{ route('harvests.show', $l->harvest_id) }}"><b>{{ $l->variety_name }}</b></a> <span class="muted">· {{ $l->farm_name }}</span><br>
          <span class="muted" style="font-size:12.5px">Harvest #{{ $l->harvest_id }} · {{ $l->harvest_date->format('M d, Y') }}</span></div>
        <div style="min-width:150px;text-align:right"><b class="mono">{{ number_format($l->quantity_available, 2) }} {{ $l->unit }}</b>
          <div class="bar {{ $l->percent_left < 25 ? 'low' : '' }}" style="margin-top:6px"><i style="width:{{ $l->percent_left }}%"></i></div></div>
      </div>
    @empty <div class="empty">All stock has been sold.</div> @endforelse
  </div>
</div>

<div class="grid g-main">
  <div class="card">
    <div class="card-h"><h2>Farm performance</h2><span class="muted">Revenue from harvests of each farm vs. its expenses</span></div>
    <div class="table-wrap"><table class="t"><thead><tr><th>Farm</th><th class="num">Area (ha)</th><th class="num">Sales revenue</th><th class="num">Expenses</th><th class="num">Net</th></tr></thead><tbody>
      @foreach($farms as $f)
        <tr><td><b>{{ $f->farm_name }}</b><br><span class="muted" style="font-size:12.5px">{{ $f->location }}</span></td><td class="num">{{ number_format($f->area_hectares, 2) }}</td>
          <td class="num">{{ $peso($f->revenue) }}</td><td class="num">{{ $peso($f->spent) }}</td>
          <td class="num"><b style="color:{{ $f->revenue - $f->spent < 0 ? 'var(--danger)' : 'var(--ok)' }}">{{ $peso($f->revenue - $f->spent) }}</b></td></tr>
      @endforeach
    </tbody></table></div>
  </div>
  <div class="card">
    <div class="card-h"><h2>Payment status</h2></div>
    <div class="chart-box" style="height:200px"><canvas id="status"></canvas></div>
    <div class="card-h" style="border-top:1px solid var(--line)"><h2>Top expenses</h2></div>
    @foreach($expenseByCategory as $c)
      <div class="list-row"><span>{{ $c->expense_category }}</span><b class="mono">{{ $peso($c->total) }}</b></div>
    @endforeach
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
  const d = @json($chart);
  if (window.Chart) {
    Chart.defaults.font.family = "'DM Sans', sans-serif";
    new Chart(document.getElementById('monthly'), {
      type: 'bar',
      data: { labels: d.labels, datasets: [
        { label: 'Sales', data: d.sales, backgroundColor: '#3f7d3c', borderRadius: 6 },
        { label: 'Expenses', data: d.expenses, backgroundColor: '#d6a62b', borderRadius: 6 }]},
      options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, scales: { y: { ticks: { callback: v => '₱' + v.toLocaleString() } } } }
    });
    const colors = { 'Paid': '#2c7a3f', 'Partially Paid': '#d6a62b', 'Unpaid': '#b3402f', 'No Items': '#9a9a85' };
    new Chart(document.getElementById('status'), {
      type: 'doughnut',
      data: { labels: d.status.labels, datasets: [{ data: d.status.data, backgroundColor: d.status.labels.map(l => colors[l] || '#999'), borderWidth: 0 }] },
      options: { maintainAspectRatio: false, cutout: '62%', plugins: { legend: { position: 'right' } } }
    });
  }
</script>
@endpush
