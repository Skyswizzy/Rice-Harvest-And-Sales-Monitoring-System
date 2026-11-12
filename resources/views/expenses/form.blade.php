@extends('layouts.app')
@section('title', $expense->exists ? 'Edit expense' : 'Record expense')
@section('content')
<div class="page-head"><div><div class="crumbs"><a href="{{ route('expenses.index') }}">Expenses</a> /</div><h1>{{ $expense->exists ? 'Edit expense' : 'Record expense' }}</h1></div></div>
<div class="card" style="max-width:820px"><form class="card-b" method="POST" action="{{ $expense->exists ? route('expenses.update', $expense) : route('expenses.store') }}">
  @csrf @if($expense->exists) @method('PUT') @endif
  <div class="form-grid">
    <x-field name="farm_id" label="Farm" type="select" required><option value="">Select farm…</option>@foreach($farms as $f)<option value="{{ $f->farm_id }}" @selected(old('farm_id', $expense->farm_id) == $f->farm_id)>{{ $f->farm_name }}</option>@endforeach</x-field>
    <x-field name="expense_date" label="Date" type="date" :value="optional($expense->expense_date)->format('Y-m-d')" :max="now()->toDateString()" required/>
    <x-field name="expense_category" label="Category" list="cats" :value="$expense->expense_category" required hint="Pick an existing category or type a new one. Spelling is matched to existing categories to avoid duplicates."/>
    <datalist id="cats">@foreach($categories as $c)<option value="{{ $c }}">@endforeach</datalist>
    <x-field name="amount" label="Amount (₱)" type="number" step="0.01" min="0.01" :value="$expense->amount" required/>
    <x-field class="span2" name="description" label="Description" type="textarea" :value="$expense->description"/>
  </div>
  <div class="form-actions"><button class="btn btn-primary">Save expense</button><a class="btn" href="{{ route('expenses.index') }}">Cancel</a></div>
</form></div>
@endsection
