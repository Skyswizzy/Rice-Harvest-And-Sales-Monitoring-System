@extends('layouts.app')
@section('title', 'Rice varieties')
@section('content')
<div class="page-head">
  <div><h1>Rice varieties</h1><p>Each variety name is stored once and referenced by harvests.</p></div>
  <div class="actions"><a class="btn btn-primary" href="{{ route('varieties.create') }}"><x-icon name="plus"/> Add variety</a></div>
</div>
<div class="card"><div class="table-wrap"><table class="t">
  <thead><tr><th>Variety</th><th>Description</th><th class="num">Harvests</th><th></th></tr></thead><tbody>
  @forelse($varieties as $v)
    <tr><td><b>{{ $v->variety_name }}</b></td><td class="muted">{{ $v->description ?: '—' }}</td>
      <td class="num"><a href="{{ route('harvests.index', ['variety_id' => $v->variety_id]) }}">{{ $v->harvests_count }}</a></td>
      <td><div class="row-actions"><a class="btn btn-sm" href="{{ route('varieties.edit', $v) }}"><x-icon name="edit"/> Edit</a>
        @if(auth()->user()->isAdmin())<form method="POST" action="{{ route('varieties.destroy', $v) }}" data-confirm="Delete {{ $v->variety_name }}?">@csrf @method('DELETE')<button class="btn btn-sm btn-danger btn-icon"><x-icon name="trash"/></button></form>@endif
      </div></td></tr>
  @empty <tr><td colspan="4" class="empty">No varieties yet.</td></tr> @endforelse
  </tbody></table></div></div>
@endsection
