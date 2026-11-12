@props(['name', 'label', 'type' => 'text', 'value' => null, 'required' => false, 'hint' => null, 'list' => null])
<div {{ $attributes->only('class')->merge(['class' => 'field']) }}>
  <label for="{{ $name }}">{{ $label }}@if($required)<span class="req">*</span>@endif</label>
  @if($type === 'textarea')
    <textarea id="{{ $name }}" name="{{ $name }}" @class(['is-invalid' => $errors->has($name)])>{{ old($name, $value) }}</textarea>
  @elseif($type === 'select')
    <select id="{{ $name }}" name="{{ $name }}" @class(['is-invalid' => $errors->has($name)]) @required($required)>{{ $slot }}</select>
  @else
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}" @if($list) list="{{ $list }}" @endif
      {{ $attributes->except('class') }} @class(['is-invalid' => $errors->has($name)]) @required($required)>
  @endif
  @if($hint)<p class="hint">{{ $hint }}</p>@endif
  @error($name)<p class="err">{{ $message }}</p>@enderror
</div>
