@extends('admin.layouts.app')

@section('title', 'Edit Travel Partner')

@section('content')
    <div class="content-area">

        <!-- ===== PAGE HEADER ===== -->
        <div class="tpe-header">
            <div>
                <h2 class="tpe-title">Edit Travel Partner</h2>
                <p class="tpe-subtitle">Update partner information and API configuration</p>
            </div>
            <a href="{{ route('admin.travel-partners.index') }}" class="tpe-back-btn">
                <i class="bi bi-arrow-left"></i> Back to partners
            </a>
        </div>

        <form action="{{ route('admin.travel-partners.update', $partner->id) }}"
              method="POST"
              enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <!-- ===== BASIC INFORMATION ===== -->
            <div class="tpe-card">
                <h5 class="tpe-section-title"><i class="bi bi-info-circle"></i> Basic Information</h5>

                <div class="tpe-grid-3">
                    <div class="tpe-field">
                        <label>Company Name <span class="tpe-required">*</span></label>
                        <input type="text" readonly name="company_name" class="form-control @error('company_name') is-invalid @enderror"
                               value="{{ old('company_name', $partner->company_name) }}" required>
                        <div class="tpe-lock-hint"><i class="bi bi-lock"></i> Cannot be changed</div>
                        @error('company_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="tpe-field">
                        <label>Module <span class="tpe-required">*</span></label>
                        <select disabled name="module_id" class="form-select @error('module_id') is-invalid @enderror" required>
                            <option value="">Select module</option>
                            @foreach($modules as $module)
                                <option value="{{ $module->id }}" {{ old('module_id', $partner->module_id) == $module->id ? 'selected' : '' }}>
                                    {{ $module->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="tpe-lock-hint"><i class="bi bi-lock"></i> Cannot be changed</div>
                        @error('module_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="tpe-field">
                        <label>Status <span class="tpe-required">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="active" {{ old('status', $partner->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $partner->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- ===== FINANCIAL INFORMATION ===== -->
            <div class="tpe-card">
                <h5 class="tpe-section-title"><i class="bi bi-cash-coin"></i> Financial Information</h5>

                <div class="tpe-grid-2">
                    <div class="tpe-field">
                        <label>Commission Rate (%)</label>
                        <input type="number" name="commission_rate" class="form-control @error('commission_rate') is-invalid @enderror"
                               value="{{ old('commission_rate', $partner->commission_rate) }}"
                               min="0" max="100" step="0.01">
                        @error('commission_rate')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="tpe-field">
                        <label>Discount Rate (%)</label>
                        <input type="number" name="discount_rate" class="form-control @error('discount_rate') is-invalid @enderror"
                               value="{{ old('discount_rate', $partner->discount_rate) }}"
                               min="0" max="100" step="0.01">
                        @error('discount_rate')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="tpe-field">
                        <label>B2C Markup (%)</label>
                        <input type="number" name="b2c_markup" class="form-control @error('b2c_markup') is-invalid @enderror"
                               value="{{ old('b2c_markup', $partner->b2c_markup) }}"
                               min="0" max="100" step="0.01">
                        @error('b2c_markup')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="tpe-field">
                        <label>B2B Markup (%)</label>
                        <input type="number" name="b2b_markup" class="form-control @error('b2b_markup') is-invalid @enderror"
                               value="{{ old('b2b_markup', $partner->b2b_markup) }}"
                               min="0" max="100" step="0.01">
                        @error('b2b_markup')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            @if($partner->company_name != "Manual")
                <!-- ===== API CONFIGURATION ===== -->
                <div class="tpe-card">
                    <h5 class="tpe-section-title"><i class="bi bi-key"></i> API Configuration</h5>

                    <!-- Development Mode Toggle -->
                    <div class="tpe-dev-card">
                        <div class="tpe-dev-left">
                            <div class="tpe-dev-icon"><i class="bi bi-code-slash"></i></div>
                            <div>
                                <div class="tpe-dev-title">Development Mode</div>
                                <div class="tpe-dev-desc">Enable test/sandbox environment for this partner</div>
                            </div>
                        </div>
                        <label class="tpe-switch">
                            <input type="checkbox"
                                   name="development_mode"
                                   value="1"
                                   id="developmentModeSwitch"
                                {{ old('development_mode', $partner->development_mode) ? 'checked' : '' }}>
                            <span class="tpe-switch-slider"></span>
                        </label>
                    </div>

                    <div class="tpe-grid-2 tpe-grid-2-spaced">
                        <div class="tpe-field">
                            <label>API Credential 1</label>
                            <input type="text" name="api_credential_1" class="form-control @error('api_credential_1') is-invalid @enderror"
                                   value="{{ old('api_credential_1', $partner->api_credential_1) }}">
                            @error('api_credential_1')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="tpe-field">
                            <label>API Credential 2</label>
                            <input type="text" name="api_credential_2" class="form-control @error('api_credential_2') is-invalid @enderror"
                                   value="{{ old('api_credential_2', $partner->api_credential_2) }}">
                            @error('api_credential_2')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="tpe-field">
                            <label>API Credential 3</label>
                            <input type="text" name="api_credential_3" class="form-control @error('api_credential_3') is-invalid @enderror"
                                   value="{{ old('api_credential_3', $partner->api_credential_3) }}">
                            @error('api_credential_3')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="tpe-field">
                            <label>API Credential 4</label>
                            <input type="text" name="api_credential_4" class="form-control @error('api_credential_4') is-invalid @enderror"
                                   value="{{ old('api_credential_4', $partner->api_credential_4) }}">
                            @error('api_credential_4')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="tpe-field">
                            <label>API Credential 5</label>
                            <input type="text" name="api_credential_5" class="form-control @error('api_credential_5') is-invalid @enderror"
                                   value="{{ old('api_credential_5', $partner->api_credential_5) }}">
                            @error('api_credential_5')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="tpe-field">
                            <label>API Credential 6</label>
                            <input type="text" name="api_credential_6" class="form-control @error('api_credential_6') is-invalid @enderror"
                                   value="{{ old('api_credential_6', $partner->api_credential_6) }}">
                            @error('api_credential_6')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            @endif

            <!-- ===== FORM ACTIONS ===== -->
            <div class="tpe-actions">
                <a href="{{ route('admin.travel-partners.index') }}" class="tpe-btn tpe-btn-outline">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
                <div class="tpe-actions-right">
                    @if($partner->status == 'active')
                        <button type="button" class="tpe-btn tpe-btn-warning" onclick="suspendPartner({{ $partner->id }})">
                            <i class="bi bi-pause-circle"></i> Suspend Partner
                        </button>
                    @else
                        <button type="button" class="tpe-btn tpe-btn-success" onclick="activatePartner({{ $partner->id }})">
                            <i class="bi bi-play-circle"></i> Activate Partner
                        </button>
                    @endif
                    <button type="submit" class="tpe-btn tpe-btn-primary">
                        <i class="bi bi-check-circle"></i> Update Partner
                    </button>
                </div>
            </div>
        </form>
    </div>

    @push('styles')
        <style>
            /* ===== PAGE HEADER ===== */
            .tpe-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 12px;
                margin-bottom: 1.25rem;
            }
            .tpe-title {
                font-size: 20px;
                font-weight: 600;
                color: var(--bs-body-color);
                margin: 0 0 4px;
            }
            .tpe-subtitle {
                font-size: 13px;
                color: var(--bs-secondary-color);
                margin: 0;
            }
            .tpe-back-btn {
                display: inline-flex;
                align-items: center;
                gap: 7px;
                padding: 8px 16px;
                border: 1px solid var(--bs-border-color);
                border-radius: 8px;
                background: var(--bs-secondary-bg);
                color: var(--bs-secondary-color);
                font-size: 13px;
                font-weight: 500;
                text-decoration: none;
                transition: background .15s, color .15s;
            }
            .tpe-back-btn:hover {
                background: var(--bs-tertiary-bg);
                color: var(--bs-body-color);
                text-decoration: none;
            }

            /* ===== CARD ===== */
            .tpe-card {
                background: var(--bs-body-bg);
                border: 1px solid var(--bs-border-color);
                border-radius: 14px;
                padding: 1.25rem 1.5rem;
                margin-bottom: 1.25rem;
            }

            /* ===== SECTION TITLE ===== */
            .tpe-section-title {
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 14px;
                font-weight: 600;
                color: var(--bs-body-color);
                margin: 0 0 1.1rem;
                padding-bottom: .85rem;
                border-bottom: 1px solid var(--bs-border-color);
            }
            .tpe-section-title i {
                font-size: 16px;
                color: var(--bs-primary);
            }

            /* ===== FIELD GRIDS ===== */
            .tpe-grid-3 {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 1rem;
            }
            .tpe-grid-2 {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 1rem;
            }
            .tpe-grid-2-spaced {
                margin-top: 1.25rem;
            }

            /* ===== FIELDS ===== */
            .tpe-field label {
                font-size: 12px;
                font-weight: 500;
                color: var(--bs-secondary-color);
                margin-bottom: 6px;
                display: block;
            }
            .tpe-required { color: #A32D2D; }

            .tpe-card .form-control,
            .tpe-card .form-select {
                border-radius: 8px;
                border-color: var(--bs-border-color);
                font-size: 13px;
            }
            .tpe-card .form-control:focus,
            .tpe-card .form-select:focus {
                border-color: var(--bs-primary);
                box-shadow: 0 0 0 3px rgba(24, 95, 165, .12);
            }
            .tpe-card .form-control:disabled,
            .tpe-card .form-control[readonly],
            .tpe-card .form-select:disabled {
                background: var(--bs-secondary-bg);
                color: var(--bs-secondary-color);
                cursor: not-allowed;
            }

            .tpe-lock-hint {
                font-size: 11px;
                color: var(--bs-secondary-color);
                margin-top: 5px;
                display: flex;
                align-items: center;
                gap: 4px;
            }

            /* ===== DEVELOPMENT MODE CARD ===== */
            .tpe-dev-card {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
                padding: 1rem 1.25rem;
                background: var(--bs-secondary-bg);
                border: 1px solid var(--bs-border-color);
                border-radius: 12px;
            }
            .tpe-dev-left {
                display: flex;
                align-items: center;
                gap: 12px;
            }
            .tpe-dev-icon {
                width: 40px;
                height: 40px;
                border-radius: 10px;
                background: #E6F1FB;
                color: #0C447C;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 18px;
                flex-shrink: 0;
            }
            [data-bs-theme="dark"] .tpe-dev-icon { background: #0c2f4d; color: #7db8f0; }

            .tpe-dev-title {
                font-size: 13px;
                font-weight: 600;
                color: var(--bs-body-color);
                margin-bottom: 2px;
            }
            .tpe-dev-desc {
                font-size: 12px;
                color: var(--bs-secondary-color);
            }

            /* ===== TOGGLE SWITCH ===== */
            .tpe-switch {
                position: relative;
                display: inline-block;
                width: 42px;
                height: 24px;
                flex-shrink: 0;
            }
            .tpe-switch input {
                opacity: 0;
                width: 0;
                height: 0;
                position: absolute;
            }
            .tpe-switch-slider {
                position: absolute;
                inset: 0;
                background: var(--bs-border-color);
                border-radius: 24px;
                cursor: pointer;
                transition: background .2s;
            }
            .tpe-switch-slider::before {
                content: "";
                position: absolute;
                width: 18px;
                height: 18px;
                left: 3px;
                top: 3px;
                background: var(--bs-body-bg);
                border-radius: 50%;
                transition: transform .2s;
            }
            .tpe-switch input:checked + .tpe-switch-slider { background: #0077BE; }
            .tpe-switch input:checked + .tpe-switch-slider::before { transform: translateX(18px); }
            [data-bs-theme="dark"] .tpe-switch input:checked + .tpe-switch-slider { background: #97C459; }

            /* ===== FORM ACTIONS ===== */
            .tpe-actions {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 12px;
            }
            .tpe-actions-right {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
            }
            .tpe-btn {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 9px 18px;
                border-radius: 8px;
                font-size: 13px;
                font-weight: 500;
                text-decoration: none;
                cursor: pointer;
                border: 1px solid var(--bs-border-color);
                transition: background .15s, color .15s, opacity .15s;
            }
            .tpe-btn-outline {
                background: var(--bs-secondary-bg);
                color: var(--bs-secondary-color);
            }
            .tpe-btn-outline:hover {
                background: var(--bs-tertiary-bg);
                color: var(--bs-body-color);
                text-decoration: none;
            }
            .tpe-btn-primary {
                background: var(--bs-primary);
                border-color: var(--bs-primary);
                color: #fff;
            }
            .tpe-btn-primary:hover { opacity: .9; color: #fff; }

            .tpe-btn-warning {
                background: #FAEEDA;
                border-color: transparent;
                color: #633806;
            }
            .tpe-btn-warning:hover { opacity: .8; color: #633806; }

            .tpe-btn-success {
                background: #EAF3DE;
                border-color: transparent;
                color: #27500A;
            }
            .tpe-btn-success:hover { opacity: .8; color: #27500A; }

            [data-bs-theme="dark"] .tpe-btn-warning { background: #2e1e05; color: #f0b054; }
            [data-bs-theme="dark"] .tpe-btn-success { background: #0a2e1a; color: #6dd499; }

            /* ===== RESPONSIVE ===== */
            @media (max-width: 992px) {
                .tpe-grid-3 { grid-template-columns: 1fr 1fr; }
            }
            @media (max-width: 768px) {
                .tpe-grid-3 { grid-template-columns: 1fr; }
                .tpe-grid-2 { grid-template-columns: 1fr; }
                .tpe-dev-card { flex-direction: column; align-items: flex-start; gap: 14px; }
            }
            @media (max-width: 480px) {
                .tpe-header { align-items: flex-start; }
                .tpe-actions { flex-direction: column-reverse; align-items: stretch; }
                .tpe-actions-right { flex-direction: column; }
                .tpe-btn { justify-content: center; }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            const BASE_URL = "{{ url('') }}";

            function suspendPartner(partnerId) {
                if (confirm('Are you sure you want to suspend this partner?')) {
                    fetch(`${BASE_URL}/admin/travel-partners/suspend/${partnerId}`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                window.location.reload();
                            }
                        });
                }
            }

            function activatePartner(partnerId) {
                if (confirm('Are you sure you want to activate this partner?')) {
                    fetch(`${BASE_URL}/admin/travel-partners/activate/${partnerId}`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                window.location.reload();
                            }
                        });
                }
            }
        </script>
    @endpush

@endsection
