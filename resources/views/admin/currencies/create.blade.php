{{-- resources/views/admin/currencies/create.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Add New Currency')

@section('content')

    <div class="crc-wrap">

        {{-- Header --}}
        <div class="crc-header">
            <div>
                <h4 class="crc-title">Add New Currency</h4>
                <p class="crc-subtitle">Create a new currency with exchange rate</p>
            </div>
            <a href="{{ route('admin.currencies.index') }}" class="crc-back-btn">
                <i class="bi bi-arrow-left"></i> Back to Currencies
            </a>
        </div>

        {{-- Form Card --}}
        <div class="crc-card">
            <div class="crc-card-head">
                <i class="bi bi-plus-circle"></i> Currency Details
            </div>
            <div class="crc-card-body">
                <form action="{{ route('admin.currencies.store') }}" method="POST" id="currencyForm">
                    @csrf

                    <div class="crc-grid">

                        {{-- Currency Name --}}
                        <div class="crc-field">
                            <label class="crc-label">Currency Name <span class="crc-req">*</span></label>
                            <input type="text" name="currency_name"
                                   class="crc-input @error('currency_name') is-invalid @enderror"
                                   value="{{ old('currency_name', $currency->currency_name ?? '') }}"
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
                                        {{ old('currency_country', $currency->currency_country ?? '') == $country ? 'selected' : '' }}>
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
                                   value="{{ old('currency_rate', $currency->currency_rate ?? '') }}"
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
                                <option value="1" {{ old('currency_status', $currency->currency_status ?? '') == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('currency_status', $currency->currency_status ?? '') == 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('currency_status')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>

                    <input type="hidden" value="0" name="currency_default">

                    {{-- Footer --}}
                    <div class="crc-footer">
                        <button type="button" class="crc-btn-cancel"
                                onclick="window.location='{{ route('admin.currencies.index') }}'">
                            <i class="bi bi-x-lg"></i> Cancel
                        </button>
                        <button type="submit" class="crc-btn-submit" id="submitBtn">
                            <i class="bi bi-check-lg"></i> Create Currency
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>

    @push('styles')
        <style>
            .crc-wrap { padding: 1.5rem; max-width: 860px; }

            .crc-header {
                display: flex; align-items: center; justify-content: space-between;
                margin-bottom: 1.5rem; flex-wrap: wrap; gap: 12px;
            }
            .crc-title   { font-size: 20px; font-weight: 600; color: #1a1a1a; margin: 0; }
            .crc-subtitle{ font-size: 13px; color: #888; margin: 3px 0 0; }

            .crc-back-btn {
                display: inline-flex; align-items: center; gap: 6px;
                padding: 8px 18px; background: #fff; color: #444;
                font-size: 13px; font-weight: 500; border-radius: 8px;
                border: 1px solid #ddd; text-decoration: none; transition: all .15s;
            }
            .crc-back-btn:hover { border-color: #aaa; color: #111; text-decoration: none; }

            .crc-card { background: #fff; border: 1px solid #e0e0e0; border-radius: 12px; overflow: hidden; }
            .crc-card-head {
                display: flex; align-items: center; gap: 8px;
                padding: .85rem 1.25rem; background: #f9f9f9;
                border-bottom: 1px solid #e8e8e8;
                font-size: 13px; font-weight: 600; color: #333;
            }
            .crc-card-body { padding: 1.75rem 1.5rem; }

            .crc-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem 1.5rem; }

            .crc-field { display: flex; flex-direction: column; gap: 6px; }
            .crc-label { font-size: 13px; font-weight: 500; color: #333; }
            .crc-req { color: #e74c3c; margin-left: 2px; }

            .crc-input {
                width: 100%; padding: 9px 12px;
                font-size: 13px; color: #1a1a1a;
                background: #fff; border: 1px solid #d0d0d0;
                border-radius: 8px; outline: none;
                transition: border-color .15s, box-shadow .15s;
                appearance: none; -webkit-appearance: none;
            }
            .crc-input:focus { border-color: #aaa; box-shadow: 0 0 0 3px rgba(0,0,0,.06); }
            .crc-input::placeholder { color: #bbb; }
            .crc-input.is-invalid { border-color: #e74c3c; }

            .crc-footer {
                display: flex; align-items: center; gap: 10px;
                margin-top: 2rem; padding-top: 1.25rem;
                border-top: 1px solid #eee;
            }
            .crc-btn-cancel {
                display: inline-flex; align-items: center; gap: 6px;
                padding: 9px 20px; background: #fff; color: #555;
                font-size: 13px; font-weight: 500; border-radius: 8px;
                border: 1px solid #ddd; cursor: pointer; transition: all .15s;
            }
            .crc-btn-cancel:hover { background: #f5f5f5; border-color: #bbb; }

            .crc-btn-submit {
                display: inline-flex; align-items: center; gap: 6px;
                padding: 9px 24px; background: #0073B9; color: #fff;
                font-size: 13px; font-weight: 500; border-radius: 8px;
                border: none; cursor: pointer; transition: background .15s;
            }
            .crc-btn-submit:hover { background: #005a91; }
            .crc-btn-submit:disabled { background: #7ab8d9; cursor: not-allowed; }

            @media (max-width: 640px) {
                .crc-grid { grid-template-columns: 1fr; }
                .crc-header { flex-direction: column; align-items: flex-start; }
                .crc-wrap { max-width: 100%; }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            const currencyForm = document.getElementById('currencyForm');
            if (currencyForm) {
                currencyForm.addEventListener('submit', function(e) {
                    const name    = document.querySelector('[name="currency_name"]').value.trim();
                    const country = document.querySelector('[name="currency_country"]').value.trim();
                    const status  = document.querySelector('[name="currency_status"]').value;
                    const rate    = document.querySelector('[name="currency_rate"]').value.trim();

                    if (!name) { e.preventDefault(); alert('Please enter the currency name.'); document.querySelector('[name="currency_name"]').focus(); return; }
                    if (!country) { e.preventDefault(); alert('Please select a country.'); document.querySelector('[name="currency_country"]').focus(); return; }
                    if (status === "") { e.preventDefault(); alert('Please select currency status.'); document.querySelector('[name="currency_status"]').focus(); return; }
                    if (!rate || isNaN(rate) || Number(rate) <= 0) { e.preventDefault(); alert('Please enter a valid currency rate.'); document.querySelector('[name="currency_rate"]').focus(); return; }

                    const btn = document.getElementById('submitBtn');
                    if (btn) { btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Saving...'; btn.disabled = true; }
                });
            }
        </script>
    @endpush

@endsection
