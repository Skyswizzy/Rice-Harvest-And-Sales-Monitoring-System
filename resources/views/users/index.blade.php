@extends('layouts.app')
@section('title', 'User accounts')
@section('content')
<div class="page-head">
  <div><h1>User accounts</h1><p>Administrators manage everything; Staff can record harvests, customers, sales, payments and expenses.</p></div>
  <div class="actions"><a class="btn btn-primary" href="{{ route('users.create') }}"><x-icon name="plus"/> Add user</a></div>
</div>
<div class="card"><div class="table-wrap"><table class="t"><thead><tr><th>Name</th><th>Username</th><th>Email</th><th>Role</th><th class="num">Records</th><th></th></tr></thead><tbody>
@foreach($users as $u)
  <tr><td><b>{{ $u->full_name }}</b>@if($u->is(auth()->user())) <span class="muted">(you)</span>@endif</td><td>{{ $u->username }}</td><td class="muted">{{ $u->email ?: '—' }}</td>
    <td><span class="badge {{ $u->role === 'Admin' ? 'b-admin' : 'b-staff' }}">{{ $u->role }}</span></td>
    <td class="num muted">{{ $u->harvests_count }} harvests · {{ $u->sales_count }} sales · {{ $u->expenses_count }} expenses</td>
    <td><div class="row-actions"><a class="btn btn-sm" href="{{ route('users.edit', $u) }}"><x-icon name="edit"/> Edit</a>
      @unless($u->is(auth()->user()))<form method="POST" action="{{ route('users.destroy', $u) }}" data-confirm="Delete {{ $u->full_name }}?">@csrf @method('DELETE')<button class="btn btn-sm btn-danger btn-icon"><x-icon name="trash"/></button></form>@endunless</div></td></tr>
@endforeach
</tbody></table></div></div>
@endsection
