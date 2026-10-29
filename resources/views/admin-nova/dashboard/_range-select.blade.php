{{-- Card-level period picker — every card follows the same dashboard range. --}}
<select class="tbp-dashboard-range-select" aria-label="Period">
    @foreach($dash['presets'] as $key => $label)
        <option value="{{ request()->fullUrlWithQuery(['range' => $key, 'from' => null, 'to' => null]) }}" {{ $dash['range'] === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
    @endforeach
    @if($dash['range'] === 'custom')<option selected>{{ $dash['range_label'] }}</option>@endif
</select>
