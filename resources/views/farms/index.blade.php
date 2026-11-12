@extends('layouts.app')
@section('title', 'Farms')
@section('content')
<div class="page-head">
  <div><h1>Farms</h1><p>Parcels managed by the farm. Each farm can have many harvests and expenses.</p></div>
  <div class="actions"><a class="btn btn-primary" href="{{ route('farms.create') }}"><x-icon name="plus"/> Add farm</a></div>
</div>
<div class="card"><div class="table-wrap"><table class="t">
  <thead><tr><th>Farm</th><th>Location</th><th class="num">Area (ha)</th><th class="num">Harvests</th><th class="num">Expenses</th><th></th></tr></thead>
  <tbody>
  @forelse($farms as $farm)
    <tr>
      <td><b>{{ $farm->farm_name }}</b></td><td>{{ $farm->location }}</td>
      <td class="num">{{ number_format($farm->area_hectares, 2) }}</td>
      <td class="num"><a href="{{ route('harvests.index', ['farm_id' => $farm->farm_id]) }}">{{ $farm->harvests_count }}</a></td>
      <td class="num">₱{{ number_format($farm->expenses_sum_amount ?? 0, 2) }}</td>
      <td><div class="row-actions">
        <a class="btn btn-sm" href="{{ route('farms.edit', $farm) }}"><x-icon name="edit"/> Edit</a>
        @if(auth()->user()->isAdmin())
        <form method="POST" action="{{ route('farms.destroy', $farm) }}" data-confirm="Delete {{ $farm->farm_name }}?">@csrf @method('DELETE')<button class="btn btn-sm btn-danger btn-icon" title="Delete"><x-icon name="trash"/></button></form>
        @endif
      </div></td>
    </tr>
  @empty <tr><td colspan="6" class="empty">No farms yet.</td></tr> @endforelse
  </tbody></table></div></div>
@endsection
