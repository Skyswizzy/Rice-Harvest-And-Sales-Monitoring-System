@extends('layouts.app')
@section('title', $customer->exists ? 'Edit customer' : 'Register customer')
@section('content')
<div class="page-head"><div><div class="crumbs"><a href="{{ route('customers.index') }}">Customers</a> /</div><h1>{{ $customer->exists ? 'Edit customer' : 'Register customer' }}</h1></div></div>
<div class="card" style="max-width:720px"><form class="card-b" method="POST" action="{{ $customer->exists ? route('customers.update', $customer) : route('customers.store') }}">
  @csrf @if($customer->exists) @method('PUT') @endif
  <div class="form-grid">
    <x-field class="span2" name="customer_name" label="Customer name" :value="$customer->customer_name" required/>
    <x-field name="contact_number" label="Contact number" :value="$customer->contact_number"/>
    <x-field class="span2" name="address" label="Address" :value="$customer->address"/>
  </div>
  <div class="form-actions"><button class="btn btn-primary">Save customer</button><a class="btn" href="{{ route('customers.index') }}">Cancel</a></div>
</form></div>
@endsection
