@props(['value'])
@php
$map = ['Paid' => 'b-paid', 'Partially Paid' => 'b-partial', 'Unpaid' => 'b-unpaid', 'No Items' => 'b-none'];
@endphp
<span class="badge {{ $map[$value] ?? 'b-gray' }}">{{ $value }}</span>
