@extends('layouts.app')
@section('title', 'Expenses')
@section('content')
<div class="page-head">
  <div><h1>Expenses</h1><p>Farming and harvesting costs charged to each farm.</p></div>
  <div class="actions"><a class="btn btn-primary" href="{{ route('expenses.create') }}"><x-icon name="plus"/> Record expense</a></div>
</div>
<div class="card kpi mb" style="max-width:340px"><div class="ico"><x-icon name="wallet"/></div><div class="lbl">Total (filtered)</div><div class="val">₱{{ number_format($total, 2) }}</div></div>
<div class="card">
  <form class="filters" method="GET">
    <div><label>Farm</label><select name="farm_id"><option value="">All farms</option>@foreach($farms as $f)<option value="{{ $f->farm_id }}" @selected(request('farm_id') == $f->farm_id)>{{ $f->farm_name }}</option>@endforeach</select></div>
    <div><label>Category</label><select name="category"><option value="">All</option>@foreach($categories as $c)<option @selected(request('category') === $c)>{{ $c }}</option>@endforeach</select></div>
    <div><label>From</label><input type="date" name="from" value="{{ request('from') }}"></div><div><label>To</label><input type="date" name="to" value="{{ request('to') }}"></div>
    <div style="flex:none;display:flex;gap:8px"><button class="btn btn-primary">Filter</button><a class="btn" href="{{ route('expenses.index') }}">Reset</a></div>
  </form>
  <div class="table-wrap"><table class="t"><thead><tr><th>Date</th><th>Farm</th><th>Category</th><th>Description</th><th>Recorded by</th><th class="num">Amount</th><th></th></tr></thead><tbody>
  @forelse($expenses as $e)
    <tr><td class="nowrap">{{ $e->expense_date->format('M d, Y') }}</td><td>{{ $e->farm->farm_name }}</td><td><span class="badge b-unit">{{ $e->expense_category }}</span></td><td class="muted">{{ $e->description ?: '—' }}</td><td class="muted">{{ $e->user->full_name }}</td>
      <td class="num"><b>₱{{ number_format($e->amount, 2) }}</b></td>
      <td><div class="row-actions"><a class="btn btn-sm" href="{{ route('expenses.edit', $e) }}"><x-icon name="edit"/></a>
        @if(auth()->user()->isAdmin())<form method="POST" action="{{ route('expenses.destroy', $e) }}" data-confirm="Delete this expense?">@csrf @method('DELETE')<button class="btn btn-sm btn-danger btn-icon"><x-icon name="trash"/></button></form>@endif</div></td></tr>
  @empty <tr><td colspan="7" class="empty">No expenses found.</td></tr> @endforelse
  </tbody></table></div>
  {{ $expenses->links('pagination.rice') }}
</div>
@endsection
