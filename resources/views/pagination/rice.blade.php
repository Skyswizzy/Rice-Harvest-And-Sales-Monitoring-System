@if ($paginator->hasPages())
<div class="pager no-print">
  <span>Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}</span>
  <span style="display:flex;gap:8px;align-items:center">
    <a class="btn btn-sm" href="{{ $paginator->previousPageUrl() ?: '#' }}" @if($paginator->onFirstPage()) aria-disabled="true" @endif>← Previous</a>
    <span>Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>
    <a class="btn btn-sm" href="{{ $paginator->nextPageUrl() ?: '#' }}" @if(!$paginator->hasMorePages()) aria-disabled="true" @endif>Next →</a>
  </span>
</div>
@endif
