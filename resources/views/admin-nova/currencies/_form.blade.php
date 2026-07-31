{{-- Expects: $currency (Currencies|null — null on create, the model on edit). --}}
<div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm overflow-hidden max-w-2xl">
    <div class="px-4 sm:px-5 py-4 border-b border-novaborder flex items-center gap-2">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M9 12h6M12 9v6"/></svg>
        <h2 class="text-sm font-semibold text-novatext">{{ $currency ? 'Edit Currency Details' : 'Currency Details' }}</h2>
    </div>
    <div class="p-4 sm:p-5">
        <form action="{{ $currency ? route('admin.currencies.update', $currency->id) : route('admin.currencies.store') }}" method="POST" id="currencyForm">
            @csrf
            @if($currency)
                @method('PATCH')
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-2">
                <div>
                    <label class="cr-label">Currency Name <span class="cr-required">*</span></label>
                    <input type="text" name="currency_name" class="cr-input @error('currency_name') is-invalid @enderror"
                           value="{{ old('currency_name', $currency->currency_name ?? '') }}" required placeholder="e.g. US Dollar">
                    @error('currency_name') <p class="cr-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="cr-label">Country <span class="cr-required">*</span></label>
                    <select name="currency_country" class="cr-input @error('currency_country') is-invalid @enderror" required>
                        <option value="">Select Country</option>
                        @foreach(['Pakistan','USA','UK','UAE','Saudi Arabia','India','Canada'] as $country)
                            <option value="{{ $country }}" {{ old('currency_country', $currency->currency_country ?? '') == $country ? 'selected' : '' }}>{{ $country }}</option>
                        @endforeach
                    </select>
                    @error('currency_country') <p class="cr-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="cr-label">Exchange Rate <span class="cr-required">*</span></label>
                    <input type="number" step="0.01" name="currency_rate" class="cr-input @error('currency_rate') is-invalid @enderror"
                           value="{{ old('currency_rate', $currency->currency_rate ?? '') }}" required placeholder="e.g. 278.50">
                    @error('currency_rate') <p class="cr-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="cr-label">Status <span class="cr-required">*</span></label>
                    <select name="currency_status" class="cr-input @error('currency_status') is-invalid @enderror" required>
                        <option value="1" {{ old('currency_status', $currency->currency_status ?? '') == 1 ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('currency_status', $currency->currency_status ?? '') == 0 ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('currency_status') <p class="cr-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <input type="hidden" name="currency_default" value="{{ $currency && $currency->currency_default ? '1' : '0' }}">

            <div class="flex items-center justify-end gap-2 pt-4 mt-2 border-t border-novaborder">
                <button type="button" class="cr-btn-nova px-4 py-2.5 text-xs font-semibold" onclick="window.location='{{ route('admin.currencies.index') }}'">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    Cancel
                </button>
                <button type="submit" class="cr-btn-nova cr-btn-primary px-4 py-2.5 text-xs font-semibold" id="submitBtn">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    {{ $currency ? 'Update Currency' : 'Create Currency' }}
                </button>
            </div>
        </form>
    </div>
</div>

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
            if (!rate || isNaN(rate) || Number(rate) <= 0) { e.preventDefault(); alert('Please enter a valid currency rate.'); form.querySelector('[name="currency_rate"]').focus(); return; }

            const btn = document.getElementById('submitBtn');
            if (btn) { btn.disabled = true; }
        });
    })();
</script>
@endpush
