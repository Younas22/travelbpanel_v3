{{-- Create / Edit Room. Expects: $room (Room|null), $hotels. Room types are listed for the selected
     hotel only (rebuilt client-side when the hotel changes). Same fields/validation as RoomController. --}}
@php
    $isEdit = (bool) $room;
    $hotelId = (string) old('hotel_id', $isEdit ? $room->hotel_id : request('hotel_id'));
    $typeId = (string) old('room_type_id', $isEdit ? $room->room_type_id : request('room_type_id', ''));
    $statusVal = old('status', $isEdit ? $room->status : 'available');
    $allTypes = \App\Models\RoomType::where(fn ($q) => $q->where('status', 1)->when($typeId !== '', fn ($q2) => $q2->orWhere('id', $typeId)))
        ->orderBy('name')->get(['id', 'hotel_id', 'name']);
    $statuses = ['available' => 'Available', 'occupied' => 'Occupied', 'maintenance' => 'Maintenance'];
@endphp

@if($errors->any())
    <div class="tbp-hotel-form-errors"><i class="bi bi-exclamation-circle"></i>
        <div><strong>Please fix the following:</strong><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    </div>
@endif

<div class="tbp-hotel-form-layout is-room">
    <form method="POST" action="{{ $isEdit ? route('admin.hotels.rooms.update', $room) : route('admin.hotels.rooms.store') }}" id="tbpRoomForm" class="tbp-hotel-form-card tbp-room-card" novalidate>
        @csrf
        @if($isEdit) @method('PATCH') @endif
        <header><i class="bi bi-card-checklist"></i><div><h2>Room Information</h2><p>Provide the details for this room.</p></div></header>

        <div class="tbp-hotel-form-grid">
            <label class="tbp-hotel-form-field"><span>Hotel <b>*</b></span>
                <span class="tbp-hotel-form-icon"><i class="bi bi-building"></i>
                    <select name="hotel_id" id="tbpRoomHotel" required data-label="Hotel">
                        <option value="">Select hotel</option>
                        @foreach($hotels as $hotel)
                            <option value="{{ $hotel->id }}" @selected($hotelId === (string) $hotel->id)>{{ $hotel->name }}</option>
                        @endforeach
                    </select></span>
                @error('hotel_id')<em class="tbp-hotel-field-error">{{ $message }}</em>@enderror</label>
            <label class="tbp-hotel-form-field"><span>Room Type <b>*</b></span>
                <span class="tbp-hotel-form-icon"><i class="bi bi-hospital"></i>
                    <select name="room_type_id" id="tbpRoomType" required data-label="Room Type" data-selected="{{ $typeId }}">
                        <option value="">Select room type</option>
                    </select></span>
                <small class="tbp-hotel-form-note" id="tbpRoomTypeHint" hidden></small>
                @error('room_type_id')<em class="tbp-hotel-field-error">{{ $message }}</em>@enderror</label>
            <label class="tbp-hotel-form-field"><span>Room Name <b>*</b></span>
                <span class="tbp-hotel-form-icon"><i class="bi bi-fonts"></i>
                    <input type="text" name="name" value="{{ old('name', $isEdit ? $room->name : '') }}" placeholder="e.g., Sea View Deluxe" maxlength="100" required data-label="Room Name"></span>
                @error('name')<em class="tbp-hotel-field-error">{{ $message }}</em>@enderror</label>
            <label class="tbp-hotel-form-field"><span>Room Number <b>*</b></span>
                <span class="tbp-hotel-form-icon"><i class="bi bi-door-closed"></i>
                    <input type="text" name="room_number" value="{{ old('room_number', $isEdit ? $room->room_number : '') }}" placeholder="e.g., 101" maxlength="20" required data-label="Room Number"></span>
                @error('room_number')<em class="tbp-hotel-field-error">{{ $message }}</em>@enderror</label>
            <label class="tbp-hotel-form-field"><span>Floor</span>
                <span class="tbp-hotel-form-icon"><i class="bi bi-bar-chart-steps"></i>
                    <input type="text" name="floor" value="{{ old('floor', $isEdit ? $room->floor : '') }}" placeholder="e.g., Ground Floor, 1st Floor" maxlength="20"></span>
                @error('floor')<em class="tbp-hotel-field-error">{{ $message }}</em>@enderror</label>
            <label class="tbp-hotel-form-field"><span>Status <b>*</b></span>
                <span class="tbp-hotel-form-icon tbp-room-status is-{{ $statusVal }}"><i class="bi bi-check-circle"></i><i class="tbp-room-dot"></i>
                    <select name="status" id="tbpRoomStatus" required data-label="Status">
                        @foreach($statuses as $v => $l)<option value="{{ $v }}" @selected($statusVal === $v)>{{ $l }}</option>@endforeach
                    </select></span></label>
        </div>

        <div class="tbp-room-banner">
            <i class="bi bi-info-circle"></i>
            <div><strong>Room Number Must Be Unique</strong><span>Each room number must be unique within the selected hotel.</span></div>
        </div>

        <footer class="tbp-room-actions">
            <a href="{{ route('admin.hotels.rooms.index', $hotelId ? ['hotel_id' => $hotelId] : []) }}" class="tbp-hotel-btn"><i class="bi bi-x-circle"></i> Cancel</a>
            <button type="submit" class="tbp-hotel-btn is-primary" id="tbpRoomSubmit"><i class="bi bi-check-circle"></i> <span>{{ $isEdit ? 'Update Room' : 'Create Room' }}</span></button>
        </footer>
    </form>

    <aside class="tbp-hotel-form-card tbp-room-info">
        <header><i class="bi bi-lightbulb"></i><div><h2>Information</h2><p>Important guidelines for adding rooms.</p></div></header>
        <ul>
            <li><i class="bi bi-hospital"></i><div><strong>Unique Room Numbers</strong><span>Room numbers must be unique within each hotel.</span></div></li>
            <li><i class="bi bi-building"></i><div><strong>Select Hotel First</strong><span>Choose the hotel to view its available room types.</span></div></li>
            <li><i class="bi bi-list-ul"></i><div><strong>Room Type</strong><span>Select the appropriate room type for this room.</span></div></li>
            <li><i class="bi bi-info-circle"></i><div><strong>Room Status</strong><span>Set the current status: Available, Occupied or Maintenance.</span></div></li>
        </ul>
    </aside>
</div>

@push('scripts')
<script>
(function () {
    var form = document.getElementById('tbpRoomForm');
    var hotel = document.getElementById('tbpRoomHotel'), type = document.getElementById('tbpRoomType'), hint = document.getElementById('tbpRoomTypeHint');
    var types = @json($allTypes);
    var createTypeUrl = @json(route('admin.hotels.room-types.create'));

    // Room types for the selected hotel only
    function fillTypes(keep) {
        var hid = hotel.value, wanted = keep ? type.dataset.selected : '';
        type.innerHTML = '';
        var first = document.createElement('option'); first.value = '';
        var list = types.filter(function (t) { return String(t.hotel_id) === hid; });
        first.textContent = !hid ? 'Select a hotel first' : (list.length ? 'Select room type' : 'No room types for this hotel');
        type.appendChild(first);
        list.forEach(function (t) {
            var o = document.createElement('option'); o.value = t.id; o.textContent = t.name;
            if (String(t.id) === String(wanted)) o.selected = true;
            type.appendChild(o);
        });
        type.disabled = !hid || !list.length;
        hint.hidden = !(hid && !list.length);
        if (!hint.hidden) hint.innerHTML = 'This hotel has no room types yet. <a href="' + createTypeUrl + '?hotel_id=' + hid + '">Add a room type</a> first.';
    }
    hotel.addEventListener('change', function () { fillTypes(false); });
    fillTypes(true);

    // Status dot colour
    var status = document.getElementById('tbpRoomStatus');
    status.addEventListener('change', function () { status.parentNode.className = 'tbp-hotel-form-icon tbp-room-status is-' + status.value; });

    // Required fields with inline messages; loading state + no double submit
    function box(el) { return el.closest('.tbp-hotel-form-field'); }
    function clearError(el) { var b = box(el); b.classList.remove('has-error'); b.querySelectorAll('.tbp-hotel-field-error').forEach(function (m) { m.remove(); }); }
    var required = form.querySelectorAll('[required][data-label]');
    required.forEach(function (el) { ['input', 'change'].forEach(function (ev) { el.addEventListener(ev, function () { if (el.value.trim()) clearError(el); }); }); });
    form.querySelectorAll('.tbp-hotel-field-error').forEach(function (m) { box(m).classList.add('has-error'); });
    form.addEventListener('submit', function (e) {
        var btn = document.getElementById('tbpRoomSubmit');
        if (btn.disabled) { e.preventDefault(); return; }
        var first = null;
        required.forEach(function (el) {
            if (!el.value.trim()) {
                clearError(el); var b = box(el); b.classList.add('has-error');
                var m = document.createElement('em'); m.className = 'tbp-hotel-field-error'; m.textContent = el.dataset.label + ' is required.'; b.appendChild(m);
                first = first || el;
            }
        });
        if (first) { e.preventDefault(); box(first).scrollIntoView({ behavior: 'smooth', block: 'center' }); first.focus({ preventScroll: true }); return; }
        btn.disabled = true; btn.querySelector('span').textContent = 'Saving…';
    });
})();
</script>
@endpush
