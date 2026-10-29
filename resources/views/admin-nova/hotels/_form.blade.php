{{-- Create / Edit Hotel form. Expects: $hotel (Hotel|null), $locations, $amenities.
     Languages come from the `languages` table (active ones, default first). Per-language
     fields post as translations[locale][field] → HotelController::syncTranslations(). --}}
@php
    $isEdit = (bool) $hotel;
    $languages = \App\Models\Language::where('status', 1)->orderByDesc('is_default')->orderBy('sort_order')->get();
    $defaultLang = optional($languages->firstWhere('is_default', true))->code ?? 'en';
    $flags = ['en' => 'gb', 'nl' => 'nl', 'fr' => 'fr', 'de' => 'de', 'es' => 'es', 'it' => 'it', 'ar' => 'ae', 'tr' => 'tr', 'ru' => 'ru', 'cn' => 'cn', 'zh' => 'cn', 'ja' => 'jp', 'ko' => 'kr', 'ur' => 'pk', 'pt' => 'pt', 'hi' => 'in'];
    $tr = $isEdit ? $hotel->translations->keyBy('locale') : collect();
    $trVal = function ($locale, $field) use ($tr, $isEdit, $hotel, $defaultLang) {
        $saved = optional($tr->get($locale))->{$field};
        if (blank($saved) && $isEdit && $locale === $defaultLang) {
            // hotels saved before translations existed: show their own fields / existing policies
            $saved = match ($field) {
                'name', 'address', 'description' => $hotel->{$field},
                'cancellation_policy' => optional($hotel->policies->firstWhere('policy_type', 'cancellation'))->description,
                'privacy_policy' => optional($hotel->policies->firstWhere('policy_type', 'privacy'))->description,
                default => null,
            };
        }
        return old("translations.$locale.$field", $saved ?? '');
    };
    $val = fn ($field, $default = '') => old($field, $isEdit ? $hotel->{$field} : $default);
    $selectedAmenities = collect(old('amenities', $isEdit ? $hotel->amenities->pluck('id')->all() : []))->map(fn ($v) => (int) $v)->all();
    $isActive = (string) old('status', $isEdit ? (int) $hotel->status : 1) === '1';
@endphp

@if($errors->any())
    <div class="tbp-hotel-form-errors"><i class="bi bi-exclamation-circle"></i>
        <div><strong>Please fix the following:</strong><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    </div>
@endif

<form method="POST" action="{{ $isEdit ? route('admin.hotels.update', $hotel) : route('admin.hotels.store') }}" enctype="multipart/form-data" id="tbpHotelForm" novalidate class="tbp-hotel-form-layout">
    @csrf
    @if($isEdit) @method('PATCH') @endif

    <div class="tbp-hotel-form-main">
        {{-- ===== BASIC INFORMATION ===== --}}
        <section class="tbp-hotel-form-card">
            <header><i class="bi bi-file-earmark-text"></i><div><h2>Basic Information</h2><p>Add general details about the hotel.</p></div></header>
            <div class="tbp-hotel-form-grid">
                <label class="tbp-hotel-form-field"><span>Hotel Name <b>*</b></span>
                    <input type="text" name="name" id="hotelBaseName" value="{{ $val('name') }}" placeholder="e.g., Grand Plaza Hotel" required maxlength="255" data-label="Hotel Name">
                    @error('name')<em class="tbp-hotel-field-error">{{ $message }}</em>@enderror</label>
                <div class="tbp-hotel-form-field"><span>Location <b>*</b></span>
                    <select name="location_id" id="location_id" class="location-select" required data-label="Location">
                        <option value="">Search and select location...</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}" @selected((string) $val('location_id') === (string) $location->id)>{{ $location->city }}, {{ $location->country }}</option>
                        @endforeach
                    </select>
                    @error('location_id')<em class="tbp-hotel-field-error">{{ $message }}</em>@enderror</div>
                <label class="tbp-hotel-form-field"><span>Stay Type <b>*</b></span>
                    <select name="type" required data-label="Stay Type">
                        <option value="">Select stay type</option>
                        @foreach(\App\Support\HotelTypes::options() + ($val('type') && !array_key_exists($val('type'), \App\Support\HotelTypes::options()) ? [$val('type') => ucwords($val('type'))] : []) as $v => $l)
                            <option value="{{ $v }}" @selected($val('type') === $v)>{{ $l }}</option>
                        @endforeach
                    </select>
                    @error('type')<em class="tbp-hotel-field-error">{{ $message }}</em>@enderror</label>
                <label class="tbp-hotel-form-field"><span>Address (Default)</span>
                    <input type="text" name="address" id="hotelBaseAddress" value="{{ $val('address') }}" placeholder="Street / Area" maxlength="255"></label>
            </div>
            <input type="hidden" name="description" id="hotelBaseDescription" value="{{ $val('description') }}">
        </section>

        {{-- ===== TRANSLATIONS ===== --}}
        <section class="tbp-hotel-form-card">
            <header><i class="bi bi-globe2"></i><div><h2>Translations</h2><p>Add information in multiple languages.</p></div>
                <small class="tbp-hotel-form-hint"><i class="bi bi-info-circle"></i> {{ strtoupper($defaultLang) }} is the default; other languages fall back to it when left empty.</small></header>
            <nav class="tbp-hotel-lang-tabs" role="tablist">
                @foreach($languages as $lang)
                    <button type="button" class="tbp-hotel-lang-tab {{ $loop->first ? 'is-active' : '' }}" data-lang-tab="{{ $lang->code }}" role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                        @if(isset($flags[$lang->code]))<span class="fi fi-{{ $flags[$lang->code] }}"></span>@endif
                        {{ $lang->name }}<i class="tbp-hotel-lang-dot" data-lang-dot="{{ $lang->code }}" hidden></i>
                    </button>
                @endforeach
            </nav>
            @foreach($languages as $lang)
                @php $code = $lang->code; $isDefault = $code === $defaultLang; @endphp
                <div class="tbp-hotel-lang-pane" data-lang-pane="{{ $code }}" dir="{{ $lang->direction === 'rtl' ? 'rtl' : 'ltr' }}" @unless($loop->first) hidden @endunless>
                    <div class="tbp-hotel-form-grid">
                        <label class="tbp-hotel-form-field"><span>Name ({{ $lang->name }}) @if($isDefault)<b>*</b>@endif</span>
                            <input type="text" name="translations[{{ $code }}][name]" value="{{ $trVal($code, 'name') }}" placeholder="Hotel name in {{ $lang->name }}" maxlength="255" @if($isDefault) data-mirror="hotelBaseName" required data-label="Name ({{ $lang->name }})" @endif></label>
                        <label class="tbp-hotel-form-field"><span>Address ({{ $lang->name }})</span>
                            <input type="text" name="translations[{{ $code }}][address]" value="{{ $trVal($code, 'address') }}" placeholder="Address in {{ $lang->name }}" maxlength="255" @if($isDefault) data-mirror="hotelBaseAddress" @endif></label>
                        <div class="tbp-hotel-form-field is-full"><span>Description ({{ $lang->name }})</span>
                            <textarea data-rich name="translations[{{ $code }}][description]" rows="3" placeholder="Enter description in {{ $lang->name }}">{{ $trVal($code, 'description') }}</textarea></div>
                        <div class="tbp-hotel-form-field is-full"><span>Cancellation Policy ({{ $lang->name }})</span>
                            <textarea data-rich name="translations[{{ $code }}][cancellation_policy]" rows="3" placeholder="Enter cancellation policy in {{ $lang->name }}">{{ $trVal($code, 'cancellation_policy') }}</textarea></div>
                        <div class="tbp-hotel-form-field is-full"><span>Privacy Policy ({{ $lang->name }})</span>
                            <textarea data-rich name="translations[{{ $code }}][privacy_policy]" rows="3" placeholder="Enter privacy policy in {{ $lang->name }}">{{ $trVal($code, 'privacy_policy') }}</textarea></div>
                    </div>
                </div>
            @endforeach
        </section>

        {{-- ===== CONTACT ===== --}}
        <section class="tbp-hotel-form-card">
            <header><i class="bi bi-telephone"></i><div><h2>Contact Information</h2><p>Provide contact details for this hotel.</p></div></header>
            <div class="tbp-hotel-form-grid is-3">
                <label class="tbp-hotel-form-field"><span>Phone</span><span class="tbp-hotel-form-icon"><i class="bi bi-telephone"></i><input type="text" name="phone" value="{{ $val('phone') }}" placeholder="e.g., +1 234 567 890" maxlength="20"></span></label>
                <label class="tbp-hotel-form-field"><span>WhatsApp</span><span class="tbp-hotel-form-icon"><i class="bi bi-whatsapp"></i><input type="text" name="whatsapp" value="{{ $val('whatsapp') }}" placeholder="e.g., +1 234 567 890" maxlength="20"></span></label>
                <label class="tbp-hotel-form-field"><span>Email</span><span class="tbp-hotel-form-icon"><i class="bi bi-envelope"></i><input type="email" name="email" value="{{ $val('email') }}" placeholder="e.g., hotel@example.com" maxlength="100"></span></label>
            </div>
        </section>

        {{-- ===== DETAILS ===== --}}
        <section class="tbp-hotel-form-card">
            <header><i class="bi bi-sliders"></i><div><h2>Hotel Details</h2><p>Check-in/out times and star rating.</p></div></header>
            <div class="tbp-hotel-form-grid is-3">
                <label class="tbp-hotel-form-field"><span>Check-in Time <b>*</b></span><input type="time" name="check_in_time" required data-label="Check-in Time" value="{{ $val('check_in_time') ? substr($val('check_in_time'), 0, 5) : '' }}">
                    @error('check_in_time')<em class="tbp-hotel-field-error">{{ $message }}</em>@enderror</label>
                <label class="tbp-hotel-form-field"><span>Check-out Time <b>*</b></span><input type="time" name="check_out_time" required data-label="Check-out Time" value="{{ $val('check_out_time') ? substr($val('check_out_time'), 0, 5) : '' }}">
                    @error('check_out_time')<em class="tbp-hotel-field-error">{{ $message }}</em>@enderror</label>
                <label class="tbp-hotel-form-field"><span>Stars</span>
                    <select name="stars"><option value="">Select stars</option>
                        @for($i = 1; $i <= 5; $i++)<option value="{{ $i }}" @selected((string) $val('stars') === (string) $i)>{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>@endfor
                    </select></label>
            </div>
        </section>
    </div>

    <aside class="tbp-hotel-form-side">
        {{-- ===== IMAGES ===== --}}
        <section class="tbp-hotel-form-card">
            <header><i class="bi bi-image"></i><div><h2>Images</h2><p>Upload hotel images (you can select multiple).</p></div></header>
            <label class="tbp-hotel-drop" id="tbpHotelDrop" for="tbpHotelImages">
                <i class="bi bi-images"></i>
                <strong>Drag &amp; drop images here</strong>
                <small>or</small>
                <span class="tbp-hotel-btn is-soft">Choose Files</span>
            </label>
            <input type="file" id="tbpHotelImages" name="images[]" accept="image/jpeg,image/png,image/gif" multiple hidden>
            <small class="tbp-hotel-form-note">Accepted formats: JPG, PNG, GIF (max 2MB each).</small>
            @if($isEdit && $hotel->images->count())
                <p class="tbp-hotel-form-sub">Uploaded <span>· drag to reorder</span> <em id="tbpReorderStatus"></em></p>
                <div class="tbp-hotel-thumbs" id="tbpExistingImages">
                    @foreach($hotel->images as $image)
                        <div class="tbp-hotel-thumb" data-id="{{ $image->id }}">
                            <img src="{{ asset('public/assets/images/' . $image->image_path) }}" alt="">
                            <button type="button" class="tbp-hotel-thumb-x js-delete-image" data-id="{{ $image->id }}" title="Delete image"><i class="bi bi-x-lg"></i></button>
                        </div>
                    @endforeach
                </div>
            @endif
            <p class="tbp-hotel-form-sub" id="tbpPendingLabel" hidden>New <span>· uploads when you save</span></p>
            <div class="tbp-hotel-thumbs" id="tbpPendingImages"></div>
        </section>

        {{-- ===== AMENITIES ===== --}}
        <section class="tbp-hotel-form-card">
            <header><i class="bi bi-gift"></i><div><h2>Stay Amenities</h2><p>Select amenities available at this hotel.</p></div>
                <a href="{{ route('admin.hotels.catalog.create', 'stay-amenities') }}" target="_blank" class="tbp-hotel-btn is-small is-primary"><i class="bi bi-plus-lg"></i> Add New Amenity</a></header>
            @if($amenities->count())
                <label class="tbp-hotel-check is-compact tbp-hotel-select-all"><input type="checkbox" id="tbpAmenitiesAll"><span>Select all</span></label>
            @endif
            <div class="tbp-hotel-amenities">
                @forelse($amenities->filter(fn ($a) => $a->status || in_array($a->id, $selectedAmenities, true)) as $amenity)
                    <label class="tbp-hotel-check is-compact"><input type="checkbox" name="amenities[]" value="{{ $amenity->id }}" @checked(in_array($amenity->id, $selectedAmenities, true))><span>{{ $amenity->name }}</span></label>
                @empty
                    <small class="tbp-hotel-form-note">No stay amenities yet — add some under Hotels → Stay amenities.</small>
                @endforelse
            </div>
        </section>

        {{-- ===== PUBLISH ===== --}}
        <section class="tbp-hotel-form-card">
            <header><i class="bi bi-gear"></i><div><h2>Publish</h2><p>Set the status to make this hotel available.</p></div></header>
            <input type="hidden" name="status" value="0">
            <label class="tbp-hotel-toggle">
                <input type="checkbox" name="status" value="1" id="tbpHotelStatus" @checked($isActive)>
                <i aria-hidden="true"></i>
                <span><strong>Hotel Active Status</strong><small>Enable this status to make the hotel available for booking.</small></span>
                <em class="tbp-hotel-badge {{ $isActive ? 'is-on' : '' }}" id="tbpHotelStatusBadge">{{ $isActive ? 'Active' : 'Inactive' }}</em>
            </label>
        </section>

        <button type="submit" class="tbp-hotel-btn is-primary is-block" id="tbpHotelSubmit"><i class="bi bi-check-circle"></i> <span>{{ $isEdit ? 'Update Hotel' : 'Create Hotel' }}</span></button>
    </aside>
</form>

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icons/7.2.3/css/flag-icons.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
(function () {
    var form = document.getElementById('tbpHotelForm');
    var csrf = document.querySelector('meta[name="csrf-token"]').content;

    $('#location_id').select2({ placeholder: 'Search and select location...', width: '100%' });

    // Rich-text editors (CKEditor 5 — the editor already used for pages and blog posts) for
    // Description / Cancellation Policy / Privacy Policy. Created per language the first time its
    // tab is shown (12 languages × 3 fields would be 36 editors up front). Each editor writes back
    // to its textarea on every change, so hidden tabs and form submit always carry current HTML.
    function initEditors(pane) {
        if (!window.ClassicEditor || pane.dataset.editors) return;
        pane.dataset.editors = '1';
        var rtl = pane.getAttribute('dir') === 'rtl';
        pane.querySelectorAll('textarea[data-rich]').forEach(function (ta) {
            ClassicEditor.create(ta, {
                toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'blockQuote', 'insertTable', '|', 'undo', 'redo'],
                language: { ui: 'en', content: rtl ? 'ar' : 'en' },
                placeholder: ta.getAttribute('placeholder') || ''
            }).then(function (editor) {
                editor.model.document.on('change:data', function () { editor.updateSourceElement(); markFilled(pane); });
            }).catch(function (err) { console.error(err); });
        });
    }

    // Language tabs — every pane stays in the form (only hidden), so all languages submit together.
    var tabs = form.querySelectorAll('[data-lang-tab]');
    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            tabs.forEach(function (t) { var on = t === tab; t.classList.toggle('is-active', on); t.setAttribute('aria-selected', on); });
            form.querySelectorAll('[data-lang-pane]').forEach(function (p) {
                p.hidden = p.dataset.langPane !== tab.dataset.langTab;
                if (!p.hidden) initEditors(p);
            });
        });
    });
    // Small dot on a tab once that language has any content.
    function markFilled(pane) {
        var filled = Array.prototype.some.call(pane.querySelectorAll('input[type=text], textarea'), function (el) { return el.value.trim() !== ''; });
        var dot = form.querySelector('[data-lang-dot="' + pane.dataset.langPane + '"]');
        if (dot) dot.hidden = !filled;
    }
    form.querySelectorAll('[data-lang-pane]').forEach(function (pane) {
        markFilled(pane);
        pane.addEventListener('input', function () { markFilled(pane); });
    });

    initEditors(form.querySelector('[data-lang-pane]:not([hidden])'));

    // Default-language name/address keep the hotel's own fields in sync (and vice versa while untouched).
    form.querySelectorAll('[data-mirror]').forEach(function (el) {
        var base = document.getElementById(el.dataset.mirror);
        var touched = el.value.trim() !== '' && el.value !== base.value;
        el.addEventListener('input', function () { touched = true; base.value = el.value; });
        if (base.type !== 'hidden') base.addEventListener('input', function () { if (!touched) { el.value = base.value; markFilled(el.closest('[data-lang-pane]')); if (el.value.trim()) clearError(el); } });
    });

    // Amenities: Select all (also reflects partial / full manual selection)
    var allBox = document.getElementById('tbpAmenitiesAll');
    if (allBox) {
        var boxes = form.querySelectorAll('input[name="amenities[]"]');
        var syncAll = function () {
            var n = Array.prototype.filter.call(boxes, function (b) { return b.checked; }).length;
            allBox.checked = n === boxes.length; allBox.indeterminate = n > 0 && n < boxes.length;
        };
        allBox.addEventListener('change', function () { boxes.forEach(function (b) { b.checked = allBox.checked; }); syncAll(); });
        boxes.forEach(function (b) { b.addEventListener('change', syncAll); });
        syncAll();
    }

    // Status badge
    var status = document.getElementById('tbpHotelStatus'), badge = document.getElementById('tbpHotelStatusBadge');
    status.addEventListener('change', function () { badge.textContent = status.checked ? 'Active' : 'Inactive'; badge.classList.toggle('is-on', status.checked); });

    // New images: drag & drop, previews, remove before upload.
    var input = document.getElementById('tbpHotelImages'), drop = document.getElementById('tbpHotelDrop');
    var pendingBox = document.getElementById('tbpPendingImages'), pendingLabel = document.getElementById('tbpPendingLabel');
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
    }
    function addFiles(list) {
        Array.prototype.forEach.call(list, function (f) {
            if (!/^image\/(jpeg|png|gif)$/.test(f.type)) return alert(f.name + ': only JPG, PNG or GIF images are allowed.');
            if (f.size > 2 * 1024 * 1024) return alert(f.name + ' is larger than 2MB.');
            files.push(f);
        });
        syncInput();
    }
    input.addEventListener('change', function () { var picked = Array.prototype.slice.call(input.files); addFiles(picked); });
    ['dragenter', 'dragover'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add('is-over'); }); });
    ['dragleave', 'drop'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.remove('is-over'); }); });
    drop.addEventListener('drop', function (e) { addFiles(e.dataTransfer.files); });

    // Existing images (edit): delete + drag to reorder, same endpoints as before.
    form.querySelectorAll('.js-delete-image').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!confirm('Delete this image?')) return;
            fetch(@json(url('admin/hotels/image')) + '/' + btn.dataset.id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (d) { if (d.success) btn.closest('.tbp-hotel-thumb').remove(); else alert(d.message || 'Could not delete the image.'); })
                .catch(function () { alert('Could not delete the image.'); });
        });
    });
    var grid = document.getElementById('tbpExistingImages');
    if (grid && window.Sortable) {
        Sortable.create(grid, { animation: 150, onEnd: function () {
            var st = document.getElementById('tbpReorderStatus'); st.textContent = 'Saving order…';
            var images = Array.prototype.map.call(grid.querySelectorAll('.tbp-hotel-thumb'), function (el, i) { return { id: +el.dataset.id, sort_order: i + 1 }; });
            fetch(@json(route('admin.hotels.reorder-images')), { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: JSON.stringify({ images: images }) })
                .then(function (r) { return r.json(); })
                .then(function (d) { st.textContent = d.success ? 'Order saved' : 'Could not save order'; })
                .catch(function () { st.textContent = 'Could not save order'; });
        } });
    }

    // Submit: loading state + no double submit.
    // Required fields: show "<Field> is required." under each empty one, open the language tab
    // that holds the first error and scroll to it. Messages clear as soon as the field is filled.
    function fieldBox(el) { return el.closest('.tbp-hotel-form-field'); }
    function clearError(el) {
        var box = fieldBox(el); if (!box) return;
        box.classList.remove('has-error');
        box.querySelectorAll('.tbp-hotel-field-error').forEach(function (m) { m.remove(); });
    }
    function showError(el, msg) {
        var box = fieldBox(el); if (!box) return;
        clearError(el);
        box.classList.add('has-error');
        var m = document.createElement('em'); m.className = 'tbp-hotel-field-error'; m.textContent = msg;
        box.appendChild(m);
    }
    var required = form.querySelectorAll('[required][data-label]');
    required.forEach(function (el) {
        el.addEventListener('input', function () { if (el.value.trim()) clearError(el); });
        el.addEventListener('change', function () { if (el.value.trim()) clearError(el); });
    });
    $('#location_id').on('select2:select', function () { clearError(this); });
    // server-side errors rendered in the page get the same red styling
    form.querySelectorAll('.tbp-hotel-field-error').forEach(function (m) { m.closest('.tbp-hotel-form-field').classList.add('has-error'); });

    form.addEventListener('submit', function (e) {
        var btn = document.getElementById('tbpHotelSubmit');
        if (btn.disabled) { e.preventDefault(); return; }

        var first = null;
        required.forEach(function (el) {
            if (!el.value.trim()) { showError(el, el.dataset.label + ' is required.'); first = first || el; }
            else clearError(el);
        });
        if (first) {
            e.preventDefault();
            var pane = first.closest('[data-lang-pane]');
            if (pane && pane.hidden) form.querySelector('[data-lang-tab="' + pane.dataset.langPane + '"]').click();
            fieldBox(first).scrollIntoView({ behavior: 'smooth', block: 'center' });
            if (first.id === 'location_id') $('#location_id').select2('open'); else first.focus({ preventScroll: true });
            return;
        }

        btn.disabled = true; btn.querySelector('span').textContent = 'Saving…';
    });
})();
</script>
@endpush
