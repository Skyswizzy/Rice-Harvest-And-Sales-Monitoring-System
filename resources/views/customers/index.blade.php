@extends('layouts.app')
@section('title', 'Customers')
@section('content')
<div class="page-head">
  <div><h1>Customers</h1><p>Rice buyers and their purchase history.</p></div>
  <div class="actions"><a class="btn btn-primary" href="{{ route('customers.create') }}"><x-icon name="plus"/> Register customer</a></div>
</div>
<div class="card">
  <form class="filters" method="GET"><div><label>Search</label><input name="q" value="{{ request('q') }}" placeholder="Name or contact number…"></div>
    <div style="flex:none;display:flex;gap:8px"><button class="btn btn-primary">Search</button><a class="btn" href="{{ route('customers.index') }}">Reset</a></div></form>
  <div class="table-wrap"><table class="t"><thead><tr><th>Customer</th><th>Contact</th><th>Address</th><th class="num">Sales</th><th></th></tr></thead><tbody>
  @forelse($customers as $c)
    <tr><td><a href="{{ route('customers.show', $c) }}"><b>{{ $c->customer_name }}</b></a></td><td>{{ $c->contact_number ?: '—' }}</td><td class="muted">{{ $c->address ?: '—' }}</td><td class="num">{{ $c->sales_count }}</td>
      <td><div class="row-actions"><a class="btn btn-sm" href="{{ route('sales.create', ['customer_id' => $c->customer_id]) }}"><x-icon name="cart"/> Sell</a>
        <a class="btn btn-sm" href="{{ route('customers.edit', $c) }}"><x-icon name="edit"/></a>
        @if(auth()->user()->isAdmin())<form method="POST" action="{{ route('customers.destroy', $c) }}" data-confirm="Delete {{ $c->customer_name }}?">@csrf @method('DELETE')<button class="btn btn-sm btn-danger btn-icon"><x-icon name="trash"/></button></form>@endif</div></td></tr>
  @empty <tr><td colspan="5" class="empty">No customers found.</td></tr> @endforelse
  </tbody></table></div>
  {{ $customers->links('pagination.rice') }}
</div>
@endsection
