{{-- Expects: $currency (Currencies|null — null on create, the model on edit). --}}
<form action="{{ $currency ? route('admin.currencies.update', $currency->id) : route('admin.currencies.store') }}" method="POST" id="currencyForm" class="tbp-admin-card tbp-admin-form">
    @csrf
    @if($currency) @method('PATCH') @endif
    <header class="tbp-admin-card-head"><h2><i class="bi bi-currency-exchange"></i> Currency Details</h2></header>
    <div class="tbp-admin-form-grid">
        <label class="tbp-admin-field"><span>Currency Name <b>*</b></span>
            <input type="text" name="currency_name" class="@error('currency_name') is-invalid @enderror" value="{{ old('currency_name', $currency->currency_name ?? '') }}" required placeholder="e.g. US Dollar">
            @error('currency_name')<small class="tbp-admin-error">{{ $message }}</small>@enderror</label>
        <div class="tbp-admin-field"><span>Country <b>*</b></span>
            @php($selectedCountry = old('currency_country', $currency->currency_country ?? ''))
            @php($countryOptions = collect($countries ?? []))
            <select name="currency_country" id="currencyCountrySelect" class="@error('currency_country') is-invalid @enderror" required aria-label="Country">
                <option value="">Select Country</option>
                @if($selectedCountry && !$countryOptions->contains('name', $selectedCountry))
                    <option value="{{ $selectedCountry }}" selected>{{ $selectedCountry }}</option>
                @endif
                @foreach($countryOptions as $country)
                    <option value="{{ $country->name }}" @selected($selectedCountry == $country->name)>{{ $country->name }}</option>
                @endforeach
            </select>
            @error('currency_country')<small class="tbp-admin-error">{{ $message }}</small>@enderror</div>
        <label class="tbp-admin-field"><span>Exchange Rate <b>*</b></span>
            <input type="number" step="any" name="currency_rate" class="@error('currency_rate') is-invalid @enderror" value="{{ old('currency_rate', $currency->currency_rate ?? '') }}" required placeholder="e.g. 278.50">
            <small>Units of this currency per 1 USD</small>
            @error('currency_rate')<small class="tbp-admin-error">{{ $message }}</small>@enderror</label>
        <label class="tbp-admin-field"><span>Status <b>*</b></span>
            <select name="currency_status" class="@error('currency_status') is-invalid @enderror" required>
                <option value="1" @selected(old('currency_status', $currency->currency_status ?? 1) == 1)>Active</option>
                <option value="0" @selected(old('currency_status', $currency->currency_status ?? 1) == 0)>Inactive</option>
            </select>
            @error('currency_status')<small class="tbp-admin-error">{{ $message }}</small>@enderror</label>
    </div>
    <input type="hidden" name="currency_default" value="{{ $currency && $currency->currency_default ? '1' : '0' }}">
    <footer class="tbp-admin-form-actions">
        <a href="{{ route('admin.currencies.index') }}" class="tbp-admin-btn"><i class="bi bi-x-lg"></i> Cancel</a>
        <button type="submit" class="tbp-admin-btn is-primary" id="submitBtn"><i class="bi bi-check-lg"></i> {{ $currency ? 'Update Currency' : 'Create Currency' }}</button>
    </footer>
</form>

@push('scripts')
<script>
    (function() {
        const form = document.getElementById('currencyForm');
        if (!form) return;
        form.addEventListener('submit', function(e) {
            const name    = form.querySelector('[name="currency_name"]').value.trim();
            const country = form.querySelector('[name="currency_country"]').value.trim();
            const status  = form.querySelector('[name="currency_status"]').value;
            const rate    = form.querySelector('[name="currency_rate"]').value.trim();

            if (!name) { e.preventDefault(); alert('Please enter the currency name.'); form.querySelector('[name="currency_name"]').focus(); return; }
            if (!country) { e.preventDefault(); alert('Please select a country.'); form.querySelector('[name="currency_country"]').focus(); return; }
            if (status === "") { e.preventDefault(); alert('Please select currency status.'); form.querySelector('[name="currency_status"]').focus(); return; }
            if (!rate || isNaN(rate) || rate === '-') { e.preventDefault(); alert('Please enter a valid currency rate.'); form.querySelector('[name="currency_rate"]').focus(); return; }

            const btn = document.getElementById('submitBtn');
            if (btn) { btn.disabled = true; }
        });
    })();
</script>
@endpush

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function () {
        $('#currencyCountrySelect').select2({
            placeholder: 'Search and select a country...',
            allowClear: false,
            width: '100%',
            dropdownParent: $('#crPage')
        });
    });
</script>
@endpush
