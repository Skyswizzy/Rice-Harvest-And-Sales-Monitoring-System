@if(session('success'))<div class="alert alert-success"><x-icon name="check"/><span>{{ session('success') }}</span></div>@endif
@if(session('error'))<div class="alert alert-error"><x-icon name="alert"/><span>{{ session('error') }}</span></div>@endif
@if($errors->any() && !isset($hideErrorSummary))
  <div class="alert alert-error"><x-icon name="alert"/><div><b>Please fix the following:</b><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div></div>
@endif
