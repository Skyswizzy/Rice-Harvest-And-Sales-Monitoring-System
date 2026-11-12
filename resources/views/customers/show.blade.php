@extends('layouts.app')
@section('title', $customer->customer_name)
@section('content')
<div class="page-head">
  <div><div class="crumbs"><a href="{{ route('customers.index') }}">Customers</a> /</div><h1>{{ $customer->customer_name }}</h1><p>{{ $customer->contact_number ?: 'No contact number' }} · {{ $customer->address ?: 'No address' }}</p></div>
  <div class="actions"><a class="btn" href="{{ route('customers.edit', $customer) }}"><x-icon name="edit"/> Edit</a><a class="btn btn-primary" href="{{ route('sales.create', ['customer_id' => $customer->customer_id]) }}"><x-icon name="cart"/> New sale</a></div>
</div>
<div class="card mb"><div class="card-b statline">
  <div><span>Total purchases</span><b>₱{{ number_format($totals['total'], 2) }}</b></div>
  <div><span>Paid</span><b>₱{{ number_format($totals['paid'], 2) }}</b></div>
  <div><span>Outstanding balance</span><b style="color:{{ $totals['balance'] > 0 ? 'var(--danger)' : 'var(--ok)' }}">₱{{ number_format($totals['balance'], 2) }}</b></div>
  <div><span>Transactions</span><b>{{ $sales->count() }}</b></div>
</div></div>
<div class="card"><div class="card-h"><h2>Purchase history</h2></div><div class="table-wrap"><table class="t">
  <thead><tr><th>Sale</th><th>Date</th><th class="num">Total</th><th class="num">Paid</th><th class="num">Balance</th><th>Status</th></tr></thead><tbody>
  @forelse($sales as $s)
    <tr><td><a href="{{ route('sales.show', $s) }}"><b>#{{ $s->sale_id }}</b></a></td><td>{{ $s->sale_date->format('M d, Y') }}</td>
      <td class="num">₱{{ number_format($s->summary->total_amount, 2) }}</td><td class="num">₱{{ number_format($s->summary->amount_paid, 2) }}</td><td class="num">₱{{ number_format($s->summary->balance, 2) }}</td>
      <td><x-status :value="$s->summary->payment_status"/></td></tr>
  @empty <tr><td colspan="6" class="empty">No purchases yet.</td></tr> @endforelse
  </tbody></table></div></div>
@endsection
