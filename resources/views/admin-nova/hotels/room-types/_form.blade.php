{{-- Create / Edit Room Type form. Expects: $roomType (RoomType|null), $hotels, $amenities.
     Reuses the Create Hotel styles (tbp-hotel-*). Posts the same fields as before to RoomTypeController. --}}
@php
    $isEdit = (bool) $roomType;
    $val = fn ($field, $default = '') => old($field, $isEdit ? $roomType->{$field} : $default);
    $hotelId = (string) old('hotel_id', $isEdit ? $roomType->hotel_id : request('hotel_id'));
    $currency = 'USD'; // room prices are entered in USD
    $selectedAmenities = collect(old('room_amenities', $isEdit ? $roomType->roomAmenities->pluck('id')->all() : []))->map(fn ($v) => (int) $v)->all();
    // Room Types master list (Hotels → Room types): active names, plus this room type's current name if it isn't in the list
    $typeNames = \App\Models\HotelCatalogItem::kind('room_type')->active()->pluck('name')->all();
    $currentName = (string) $val('name');
    if ($currentName !== '' && !in_array($currentName, $typeNames, true)) { array_unshift($typeNames, $currentName); }
    $roomAmenities = \App\Models\HotelCatalogItem::kind('room_amenity')->get()->filter(fn ($a) => $a->status || in_array($a->id, $selectedAmenities, true));
    $isActive = (string) old('status', $isEdit ? (int) $roomType->status : 1) === '1';
    $policy = old('cancellation_type', $isEdit ? $roomType->cancellation_type : 'refundable') ?: 'refundable';
    $feeType = old('cancellation_fee_type', $isEdit ? $roomType->cancellation_fee_type : 'percent') ?: 'percent';
    $freeTime = substr((string) old('free_cancel_time', $isEdit && $roomType->free_cancel_time ? $roomType->free_cancel_time : '12:00'), 0, 5);
@endphp

@if($errors->any())
    <div class="tbp-hotel-form-errors"><i class="bi bi-exclamation-circle"></i>
        <div><strong>Please fix the following:</strong><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    </div>
@endif

<form method="POST" action="{{ $isEdit ? route('admin.hotels.room-types.update', $roomType) : route('admin.hotels.room-types.store') }}" enctype="multipart/form-data" id="tbpRoomTypeForm" class="tbp-hotel-form-layout is-room-type" novalidate>
    @csrf
    @if($isEdit) @method('PATCH') @endif

    <div class="tbp-hotel-form-main">
        <section class="tbp-hotel-form-card">
            <header><i class="bi bi-file-earmark-text"></i><div><h2>Basic Information</h2><p>Provide basic details for this room type.</p></div></header>
            <div class="tbp-hotel-form-grid">
                <label class="tbp-hotel-form-field"><span>Hotel <b>*</b></span>
                    <span class="tbp-hotel-form-icon"><i class="bi bi-building"></i>
                        <select name="hotel_id" id="tbpRtHotel" required data-label="Hotel">
                            <option value="">Select hotel</option>
                            @foreach($hotels as $hotel)
                                <option value="{{ $hotel->id }}" @selected($hotelId === (string) $hotel->id)>{{ $hotel->name }}</option>
                            @endforeach
                        </select></span>
                    @error('hotel_id')<em class="tbp-hotel-field-error">{{ $message }}</em>@enderror</label>
                <label class="tbp-hotel-form-field"><span>Room Type Name <b>*</b></span>
                    <span class="tbp-hotel-form-icon"><i class="bi bi-door-closed"></i>
                        <select name="name" id="tbpRtName" required data-label="Room Type Name">
                            <option value="">Select room type</option>
                            @foreach($typeNames as $typeName)
                                <option value="{{ $typeName }}" @selected($currentName === $typeName)>{{ $typeName }}</option>
                            @endforeach
                        </select></span>
                    <small class="tbp-hotel-form-note">Missing a name? <a href="{{ route('admin.hotels.catalog.create', 'room-types') }}" target="_blank">Add it under Hotels → Room types</a>.</small>
                    @error('name')<em class="tbp-hotel-field-error">{{ $message }}</em>@enderror</label>
                <div class="tbp-hotel-form-field is-full"><span>Description</span>
                    <textarea name="description" id="tbpRtDescription" rows="4" placeholder="Enter room type description...">{{ $val('description') }}</textarea></div>
            </div>
        </section>

        <section class="tbp-hotel-form-card">
            <header><i class="bi bi-tag"></i><div><h2>Pricing &amp; Capacity</h2><p>Set price and capacity details.</p></div></header>
            <div class="tbp-hotel-form-grid">
                <label class="tbp-hotel-form-field"><span>Price Per Night <b>*</b></span>
                    <span class="tbp-hotel-form-icon is-text"><i>{{ $currency }}</i>
                        <input type="number" name="price_per_night" value="{{ $val('price_per_night') }}" min="0" step="0.01" placeholder="e.g., 120.00" required data-label="Price Per Night"></span>
                    @error('price_per_night')<em class="tbp-hotel-field-error">{{ $message }}</em>@enderror</label>
                <label class="tbp-hotel-form-field"><span>Non-refundable Price Per Night</span>
                    <span class="tbp-hotel-form-icon is-text"><i>{{ $currency }}</i>
                        <input type="number" name="non_refundable_price" value="{{ $val('non_refundable_price') }}" min="0" step="0.01" placeholder="Optional, e.g., 100.00"></span>
                    <small class="tbp-hotel-form-note">Leave empty if this room has no non-refundable rate. Per-date prices are set in the Rate calendar.</small>
                    @error('non_refundable_price')<em class="tbp-hotel-field-error">{{ $message }}</em>@enderror</label>
                <label class="tbp-hotel-form-field"><span>Number of Beds <b>*</b></span>
                    <span class="tbp-hotel-form-icon"><i class="bi bi-hospital"></i>
                        <input type="number" name="beds" value="{{ $val('beds', 1) }}" min="1" step="1" required data-label="Number of Beds"></span>
                    @error('beds')<em class="tbp-hotel-field-error">{{ $message }}</em>@enderror</label>
                <label class="tbp-hotel-form-field"><span>Max Adults <b>*</b></span>
                    <span class="tbp-hotel-form-icon"><i class="bi bi-people"></i>
                        <input type="number" name="max_adults" value="{{ $val('max_adults', 2) }}" min="1" step="1" required data-label="Max Adults"></span>
                    @error('max_adults')<em class="tbp-hotel-field-error">{{ $message }}</em>@enderror</label>
                <label class="tbp-hotel-form-field"><span>Max Children <b>*</b></span>
                    <span class="tbp-hotel-form-icon"><i class="bi bi-person"></i>
                        <input type="number" name="max_children" value="{{ $val('max_children', 0) }}" min="0" step="1" required data-label="Max Children"></span>
                    @error('max_children')<em class="tbp-hotel-field-error">{{ $message }}</em>@enderror</label>
            </div>
        </section>

        <section class="tbp-hotel-form-card" id="tbpRtPolicy">
            <header><i class="bi bi-arrow-counterclockwise"></i><div><h2>Refund Policy</h2><p>Can guests cancel this room for free, and what does a cancellation cost?</p></div></header>
            <div class="tbp-rt-policy" role="radiogroup" aria-label="Refund policy">
                <label class="tbp-rt-policy-opt"><input type="radio" name="cancellation_type" value="refundable" @checked($policy === 'refundable')>
                    <span><i class="bi bi-check-circle"></i><strong>Refundable</strong><small>Free cancellation until a set time before check-in.</small></span></label>
                <label class="tbp-rt-policy-opt"><input type="radio" name="cancellation_type" value="non_refundable" @checked($policy === 'non_refundable')>
                    <span><i class="bi bi-x-circle"></i><strong>Non-refundable</strong><small>The cancellation fee applies whenever the booking is cancelled.</small></span></label>
            </div>
            <div class="tbp-hotel-form-grid" data-policy="refundable">
                <label class="tbp-hotel-form-field"><span>Free cancellation until (days before check-in) <b>*</b></span>
                    <span class="tbp-hotel-form-icon"><i class="bi bi-calendar-event"></i>
                        <input type="number" name="free_cancel_days" value="{{ old('free_cancel_days', $isEdit ? $roomType->free_cancel_days : 1) }}" min="0" max="365" step="1" required data-label="Days before check-in"></span>
                    @error('free_cancel_days')<em class="tbp-hotel-field-error">{{ $message }}</em>@enderror</label>
                <label class="tbp-hotel-form-field"><span>At time <b>*</b></span>
                    <span class="tbp-hotel-form-icon"><i class="bi bi-clock"></i>
                        <input type="time" name="free_cancel_time" value="{{ $freeTime }}" required data-label="Time"></span>
                    @error('free_cancel_time')<em class="tbp-hotel-field-error">{{ $message }}</em>@enderror</label>
                <p class="tbp-hotel-form-note is-full" id="tbpRtPolicyExample"></p>
            </div>
            <div class="tbp-hotel-form-grid">
                <label class="tbp-hotel-form-field"><span id="tbpRtFeeLabel">Cancellation fee</span>
                    <span class="tbp-hotel-form-icon is-text"><i id="tbpRtFeeUnit">{{ $feeType === 'fixed' ? 'USD' : '%' }}</i>
                        <input type="number" name="cancellation_fee" id="tbpRtFee" value="{{ old('cancellation_fee', $isEdit ? (float) $roomType->cancellation_fee : 0) }}" min="0" step="0.01" required data-label="Cancellation fee"></span>
                    @error('cancellation_fee')<em class="tbp-hotel-field-error">{{ $message }}</em>@enderror</label>
                <label class="tbp-hotel-form-field"><span>Fee type</span>
                    <span class="tbp-hotel-form-icon"><i class="bi bi-percent"></i>
                        <select name="cancellation_fee_type" id="tbpRtFeeType">
                            <option value="percent" @selected($feeType === 'percent')>Percentage of booking total</option>
                            <option value="fixed" @selected($feeType === 'fixed')>Fixed amount (USD)</option>
                        </select></span></label>
            </div>
        </section>

        <section class="tbp-hotel-form-card">
            <header><i class="bi bi-lamp"></i><div><h2>Room Amenities</h2><p>Select in-room amenities for this room type.</p></div>
                <a href="{{ route('admin.hotels.catalog.create', 'room-amenities') }}" target="_blank" class="tbp-hotel-btn is-small is-primary"><i class="bi bi-plus-lg"></i> Add Room Amenity</a></header>
            @if($roomAmenities->count())
                <input type="hidden" name="room_amenities_present" value="1">
                <label class="tbp-hotel-check is-compact tbp-hotel-select-all"><input type="checkbox" id="tbpRtAmenitiesAll"><span>Select all</span></label>
            @endif
            <div class="tbp-hotel-amenities">
                @forelse($roomAmenities as $amenity)
                    <label class="tbp-hotel-check is-compact"><input type="checkbox" name="room_amenities[]" value="{{ $amenity->id }}" @checked(in_array($amenity->id, $selectedAmenities, true))>
                        <span class="tbp-hotel-amenity"><i class="{{ $amenity->icon ?: 'bi bi-check2' }}"></i> {{ $amenity->name }}</span></label>
                @empty
                    <small class="tbp-hotel-form-note">No room amenities yet — add some under Hotels → Room amenities.</small>
                @endforelse
            </div>
        </section>
    </div>

    <aside class="tbp-hotel-form-side">
        <section class="tbp-hotel-form-card">
            <header><i class="bi bi-gear"></i><div><h2>Status &amp; Features</h2><p>Configure availability and key features.</p></div></header>
            <div class="tbp-hotel-form-grid is-1">
                <label class="tbp-hotel-form-field"><span>Air Conditioning <b>*</b></span>
                    <span class="tbp-hotel-form-icon"><i class="bi bi-snow"></i>
                        <select name="ac" required data-label="Air Conditioning">
                            <option value="1" @selected((string) $val('ac', 1) === '1')>Yes</option>
                            <option value="0" @selected((string) $val('ac', 1) === '0')>No</option>
                        </select></span></label>
                <label class="tbp-hotel-form-field"><span>Status <b>*</b></span>
                    <span class="tbp-hotel-form-icon tbp-rt-status {{ $isActive ? 'is-active' : '' }}"><i class="tbp-rt-dot"></i>
                        <select name="status" id="tbpRtStatus" required data-label="Status">
                            <option value="1" @selected($isActive)>Active</option>
                            <option value="0" @selected(!$isActive)>Inactive</option>
                        </select></span></label>
            </div>
        </section>

        <section class="tbp-hotel-form-card">
            <header><i class="bi bi-image"></i><div><h2>Room Images</h2><p>Upload images for this room type.</p></div></header>
            <label class="tbp-hotel-drop" id="tbpRtDrop" for="tbpRtImages">
                <i class="bi bi-images"></i>
                <strong>Drag &amp; drop images here</strong>
                <small>or</small>
                <span class="tbp-hotel-btn is-soft"><i class="bi bi-cloud-arrow-up"></i> Choose Files</span>
            </label>
            <input type="file" id="tbpRtImages" name="images[]" accept="image/jpeg,image/png,image/gif" multiple hidden>
            <small class="tbp-hotel-form-note">Accepted formats: JPG, PNG, GIF (max 2MB each). You can select multiple images.</small>
            @if($isEdit && $roomType->images->count())
                <p class="tbp-hotel-form-sub">Uploaded <span>· drag to reorder</span> <em id="tbpRtReorderStatus"></em></p>
                <div class="tbp-hotel-thumbs" id="tbpRtExisting">
                    @foreach($roomType->images as $image)
                        <div class="tbp-hotel-thumb" data-id="{{ $image->id }}">
                            <img src="{{ asset('public/assets/images/' . $image->image_path) }}" alt="">
                            <button type="button" class="tbp-hotel-thumb-x js-rt-delete-image" data-id="{{ $image->id }}" title="Delete image"><i class="bi bi-x-lg"></i></button>
                        </div>
                    @endforeach
                </div>
            @endif
            <p class="tbp-hotel-form-sub" id="tbpRtPendingLabel" hidden>New <span>· uploads when you save</span></p>
            <div class="tbp-hotel-thumbs" id="tbpRtPending"></div>
        </section>

        <button type="submit" class="tbp-hotel-btn is-primary is-block" id="tbpRtSubmit"><i class="bi bi-check-circle"></i> <span>{{ $isEdit ? 'Update Room Type' : 'Create Room Type' }}</span></button>
    </aside>
</form>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
(function () {
    var form = document.getElementById('tbpRoomTypeForm');

    // Description: rich-text editor (CKEditor 5 — same editor as the hotel form); keeps the textarea in sync
    if (window.ClassicEditor) {
        ClassicEditor.create(document.getElementById('tbpRtDescription'), {
            toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'blockQuote', 'insertTable', '|', 'undo', 'redo'],
            placeholder: 'Enter room type description...'
        }).then(function (editor) {
            editor.model.document.on('change:data', function () { editor.updateSourceElement(); });
        }).catch(function (err) { console.error(err); });
    }

    // Searchable Hotel and Room Type dropdowns (select2); keep the inline-error clearing working
    $('#tbpRtHotel').select2({ placeholder: 'Search and select hotel...', width: '100%' });
    $('#tbpRtName').select2({ placeholder: 'Search and select room type...', width: '100%' });
    $('#tbpRtHotel, #tbpRtName').on('select2:select', function () { this.dispatchEvent(new Event('change')); });
    var csrf = document.querySelector('meta[name="csrf-token"]').content;

    // Amenities: Select all
    var allBox = document.getElementById('tbpRtAmenitiesAll');
    if (allBox) {
        var boxes = form.querySelectorAll('input[name="room_amenities[]"]');
        var syncAll = function () {
            var n = Array.prototype.filter.call(boxes, function (b) { return b.checked; }).length;
            allBox.checked = n === boxes.length; allBox.indeterminate = n > 0 && n < boxes.length;
        };
        allBox.addEventListener('change', function () { boxes.forEach(function (b) { b.checked = allBox.checked; }); syncAll(); });
        boxes.forEach(function (b) { b.addEventListener('change', syncAll); });
        syncAll();
    }

    // Status indicator
    var status = document.getElementById('tbpRtStatus');
    status.addEventListener('change', function () { status.parentNode.classList.toggle('is-active', status.value === '1'); });

    // New images: drag & drop, previews, remove before upload
    var input = document.getElementById('tbpRtImages'), drop = document.getElementById('tbpRtDrop');
    var pendingBox = document.getElementById('tbpRtPending'), pendingLabel = document.getElementById('tbpRtPendingLabel');
    var files = [];
    function syncInput() {
        var dt = new DataTransfer();
        files.forEach(function (f) { dt.items.add(f); });
        input.files = dt.files;
        pendingBox.innerHTML = '';
        pendingLabel.hidden = !files.length;
        files.forEach(function (f, i) {
            var div = document.createElement('div'); div.className = 'tbp-hotel-thumb is-pending';
            var img = document.createElement('img'); img.src = URL.createObjectURL(f); img.alt = f.name;
            var x = document.createElement('button'); x.type = 'button'; x.className = 'tbp-hotel-thumb-x'; x.title = 'Remove'; x.innerHTML = '<i class="bi bi-x-lg"></i>';
            x.addEventListener('click', function () { files.splice(i, 1); syncInput(); });
            div.appendChild(img); div.appendChild(x); pendingBox.appendChild(div);
        });
        if (files.length) {
            var more = document.createElement('label'); more.className = 'tbp-hotel-thumb is-add'; more.htmlFor = 'tbpRtImages';
            more.innerHTML = '<i class="bi bi-plus-lg"></i><span>Add More</span>';
            pendingBox.appendChild(more);
        }
    }
    function addFiles(list) {
        Array.prototype.forEach.call(list, function (f) {
            if (!/^image\/(jpeg|png|gif)$/.test(f.type)) return alert(f.name + ': only JPG, PNG or GIF images are allowed.');
            if (f.size > 2 * 1024 * 1024) return alert(f.name + ' is larger than 2MB.');
            files.push(f);
        });
        syncInput();
    }
    input.addEventListener('change', function () { addFiles(Array.prototype.slice.call(input.files)); });
    ['dragenter', 'dragover'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add('is-over'); }); });
    ['dragleave', 'drop'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.remove('is-over'); }); });
    drop.addEventListener('drop', function (e) { addFiles(e.dataTransfer.files); });

    // Existing images (edit): delete + reorder, same endpoints as before
    form.querySelectorAll('.js-rt-delete-image').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!confirm('Delete this image?')) return;
            fetch(@json(url('admin/hotels/room-types/image')) + '/' + btn.dataset.id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (d) { if (d.success) btn.closest('.tbp-hotel-thumb').remove(); else alert(d.message || 'Could not delete the image.'); })
                .catch(function () { alert('Could not delete the image.'); });
        });
    });
    var grid = document.getElementById('tbpRtExisting');
    if (grid && window.Sortable) {
        Sortable.create(grid, { animation: 150, onEnd: function () {
            var st = document.getElementById('tbpRtReorderStatus'); st.textContent = 'Saving order…';
            var images = Array.prototype.map.call(grid.querySelectorAll('.tbp-hotel-thumb'), function (el, i) { return { id: +el.dataset.id, sort_order: i + 1 }; });
            fetch(@json(route('admin.hotels.room-types.reorder-images')), { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: JSON.stringify({ images: images }) })
                .then(function (r) { return r.json(); })
                .then(function (d) { st.textContent = d.success ? 'Order saved' : 'Could not save order'; })
                .catch(function () { st.textContent = 'Could not save order'; });
        } });
    }

    // Refund policy: refundable shows the free-cancellation deadline; the fee label/unit follow the choices
    (function () {
        var card = document.getElementById('tbpRtPolicy'), refBox = card.querySelector('[data-policy="refundable"]');
        var feeType = document.getElementById('tbpRtFeeType'), fee = document.getElementById('tbpRtFee');
        var days = card.querySelector('[name="free_cancel_days"]'), time = card.querySelector('[name="free_cancel_time"]');
        function update() {
            var refundable = card.querySelector('[name="cancellation_type"]:checked').value === 'refundable';
            refBox.hidden = !refundable;
            refBox.querySelectorAll('input').forEach(function (i) { i.disabled = !refundable; });
            var pct = feeType.value === 'percent';
            document.getElementById('tbpRtFeeUnit').textContent = pct ? '%' : 'USD';
            fee.max = pct ? 100 : '';
            document.getElementById('tbpRtFeeLabel').textContent = refundable ? 'Fee after the free-cancellation deadline' : 'Cancellation fee';
            var d = parseInt(days.value, 10) || 0;
            document.getElementById('tbpRtPolicyExample').textContent = 'Guests can cancel for free until ' + (d ? d + ' day' + (d === 1 ? '' : 's') + ' before check-in' : 'the check-in day') + ', ' + (time.value || '12:00') + '.';
        }
        card.addEventListener('change', update); card.addEventListener('input', update);
        update();
    })();

    // Required fields + number ranges, shown under each field; loading state, no double submit
    function box(el) { return el.closest('.tbp-hotel-form-field'); }
    function clearError(el) { var b = box(el); b.classList.remove('has-error'); b.querySelectorAll('.tbp-hotel-field-error').forEach(function (m) { m.remove(); }); }
    function showError(el, msg) { clearError(el); var b = box(el); b.classList.add('has-error'); var m = document.createElement('em'); m.className = 'tbp-hotel-field-error'; m.textContent = msg; b.appendChild(m); }
    var required = form.querySelectorAll('[required][data-label]');
    required.forEach(function (el) { ['input', 'change'].forEach(function (ev) { el.addEventListener(ev, function () { if (el.value.trim()) clearError(el); }); }); });
    form.querySelectorAll('.tbp-hotel-field-error').forEach(function (m) { box(m).classList.add('has-error'); });

    form.addEventListener('submit', function (e) {
        var btn = document.getElementById('tbpRtSubmit');
        if (btn.disabled) { e.preventDefault(); return; }
        var first = null;
        required.forEach(function (el) {
            if (el.disabled) { clearError(el); return; } // e.g. free-cancellation fields on a non-refundable room
            var v = el.value.trim(), msg = null;
            if (!v) msg = el.dataset.label + ' is required.';
            else if (el.type === 'number' && el.min !== '' && Number(v) < Number(el.min)) msg = el.dataset.label + ' must be at least ' + el.min + '.';
            if (msg) { showError(el, msg); first = first || el; } else clearError(el);
        });
        if (first) { e.preventDefault(); box(first).scrollIntoView({ behavior: 'smooth', block: 'center' }); if (first.id === 'tbpRtHotel' || first.id === 'tbpRtName') { $(first).select2('open'); } else { first.focus({ preventScroll: true }); } return; }
        btn.disabled = true; btn.querySelector('span').textContent = 'Saving…';
    });
})();
</script>
@endpush
