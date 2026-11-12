@extends('layouts.app')
@section('title', $user->exists ? 'Edit user' : 'Add user')
@section('content')
<div class="page-head"><div><div class="crumbs"><a href="{{ route('users.index') }}">User accounts</a> /</div><h1>{{ $user->exists ? 'Edit user' : 'Add user' }}</h1></div></div>
<div class="card" style="max-width:820px"><form class="card-b" method="POST" action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}">
  @csrf @if($user->exists) @method('PUT') @endif
  <div class="form-grid">
    <x-field class="span2" name="full_name" label="Full name" :value="$user->full_name" required/>
    <x-field name="username" label="Username" :value="$user->username" required hint="Unique. Letters, numbers, dashes and underscores."/>
    <x-field name="email" label="Email" type="email" :value="$user->email" hint="Optional, but must be unique."/>
    <x-field name="role" label="Role" type="select" required>@foreach(['Staff' => 'Staff', 'Admin' => 'Admin (Farm Owner)'] as $k => $l)<option value="{{ $k }}" @selected(old('role', $user->role ?? 'Staff') === $k)>{{ $l }}</option>@endforeach</x-field>
    <div></div>
    <x-field name="password" :label="$user->exists ? 'New password' : 'Password'" type="password" :required="!$user->exists" autocomplete="new-password" :hint="$user->exists ? 'Leave blank to keep the current password.' : 'At least 8 characters.'"/>
    <x-field name="password_confirmation" label="Confirm password" type="password" autocomplete="new-password"/>
  </div>
  <div class="form-actions"><button class="btn btn-primary">Save user</button><a class="btn" href="{{ route('users.index') }}">Cancel</a></div>
</form></div>
@endsection
