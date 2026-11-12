{{-- One editable sale line. $i = index (or __i__ in the JS template), $item = old input / [] --}}
<div class="line">
  <div class="field">
    <select name="items[{{ $i }}][harvest_id]" required>
      <option value="">Select harvest lot…</option>
      @foreach($lots as $l)
        <option value="{{ $l->harvest_id }}" data-available="{{ $l->quantity_available }}" data-unit="{{ $l->unit }}" @selected(($item['harvest_id'] ?? null) == $l->harvest_id)>
          #{{ $l->harvest_id }} · {{ $l->variety_name }} · {{ $l->farm_name }} · {{ number_format($l->quantity_available, 2) }} {{ $l->unit }} left
        </option>
      @endforeach
    </select>
    <span class="unit-tag"></span>
    @error("items.$i.harvest_id")<p class="err">{{ $message }}</p>@enderror
  </div>
  <div class="field"><input class="qty" type="number" step="0.01" min="0.01" name="items[{{ $i }}][quantity_sold]" value="{{ $item['quantity_sold'] ?? '' }}" placeholder="Quantity" required>
    <span class="unit-tag unit-lbl"></span>@error("items.$i.quantity_sold")<p class="err">{{ $message }}</p>@enderror</div>
  <div class="field"><input class="price" type="number" step="0.01" min="0.01" name="items[{{ $i }}][unit_price]" value="{{ $item['unit_price'] ?? '' }}" placeholder="Price per unit" required>
    @error("items.$i.unit_price")<p class="err">{{ $message }}</p>@enderror</div>
  <div class="sub">₱0.00</div>
  <button type="button" class="btn btn-sm btn-danger btn-icon remove-line" title="Remove"><x-icon name="trash"/></button>
</div>
