@extends('admin.layouts.app')

@section('title', 'Edit Currency')

@section('content')

    <div class="crc-wrap">

        {{-- Header --}}
        <div class="crc-header">
            <div>
                <h4 class="crc-title">Edit Currency</h4>
                <p class="crc-subtitle">Update currency details and exchange rate</p>
            </div>
            <a href="{{ route('admin.currencies.index') }}" class="crc-back-btn">
                <i class="bi bi-arrow-left"></i> Back to Currencies
            </a>
        </div>

        {{-- Form Card --}}
        <div class="crc-card">
            <div class="crc-card-head">
                <i class="bi bi-pencil-square"></i> Edit Currency Details
            </div>
            <div class="crc-card-body">
                <form action="{{ route('admin.currencies.update', $currency->id) }}" method="POST" id="editcurrencyForm">
                    @csrf
                    @method('PATCH')

                    <div class="crc-grid">

                        {{-- Currency Name --}}
                        <div class="crc-field">
                            <label class="crc-label">Currency Name <span class="crc-req">*</span></label>
                            <input type="text" name="currency_name"
                                   class="crc-input @error('currency_name') is-invalid @enderror"
                                   value="{{ old('currency_name', $currency->currency_name) }}"
                                   required placeholder="e.g. US Dollar">
                            @error('currency_name')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Currency Country --}}
                        <div class="crc-field">
                            <label class="crc-label">Country <span class="crc-req">*</span></label>
                            <select name="currency_country"
                                    class="crc-input @error('currency_country') is-invalid @enderror"
                                    required>
                                <option value="">Select Country</option>
                                @foreach(['Pakistan','USA','UK','UAE','Saudi Arabia','India','Canada'] as $country)
                                    <option value="{{ $country }}"
                                        {{ old('currency_country', $currency->currency_country) == $country ? 'selected' : '' }}>
                                        {{ $country }}
                                    </option>
                                @endforeach
                            </select>
                            @error('currency_country')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Currency Rate --}}
                        <div class="crc-field">
                            <label class="crc-label">Exchange Rate <span class="crc-req">*</span></label>
                            <input type="number" step="0.01" name="currency_rate"
                                   class="crc-input @error('currency_rate') is-invalid @enderror"
                                   value="{{ old('currency_rate', $currency->currency_rate) }}"
                                   required placeholder="e.g. 278.50">
                            @error('currency_rate')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Status --}}
                        <div class="crc-field">
                            <label class="crc-label">Status <span class="crc-req">*</span></label>
                            <select name="currency_status"
                                    class="crc-input @error('currency_status') is-invalid @enderror"
                                    required>
                                <option value="1" {{ old('currency_status', $currency->currency_status) == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('currency_status', $currency->currency_status) == 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('currency_status')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>

                    <input type="hidden" name="currency_default" value="{{ isset($currency) && $currency->currency_default ? '1' : '0' }}">

                    {{-- Footer --}}
                    <div class="crc-footer">
                        <button type="button" class="crc-btn-cancel"
                                onclick="window.location='{{ route('admin.currencies.index') }}'">
                            <i class="bi bi-x-lg"></i> Cancel
                        </button>
                        <button type="submit" class="crc-btn-submit" id="submitBtn">
                            <i class="bi bi-check-lg"></i> Update Currency
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>

    @push('scripts')
        <script>
            const editcurrencyForm = document.getElementById('editcurrencyForm');
            if (editcurrencyForm) {
                editcurrencyForm.addEventListener('submit', function(e) {
                    const name    = document.querySelector('[name="currency_name"]').value.trim();
                    const country = document.querySelector('[name="currency_country"]').value.trim();
                    const status  = document.querySelector('[name="currency_status"]').value;
                    const rate    = document.querySelector('[name="currency_rate"]').value.trim();

                    if (!name) { e.preventDefault(); alert('Please enter the currency name.'); document.querySelector('[name="currency_name"]').focus(); return; }
                    if (!country) { e.preventDefault(); alert('Please select a country.'); document.querySelector('[name="currency_country"]').focus(); return; }
                    if (status === "") { e.preventDefault(); alert('Please select currency status.'); document.querySelector('[name="currency_status"]').focus(); return; }
                    if (!rate || isNaN(rate) || Number(rate) <= 0) { e.preventDefault(); alert('Please enter a valid currency rate.'); document.querySelector('[name="currency_rate"]').focus(); return; }

                    const btn = document.getElementById('submitBtn');
                    if (btn) { btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Updating...'; btn.disabled = true; }
                });
            }
        </script>
    @endpush

@endsection
