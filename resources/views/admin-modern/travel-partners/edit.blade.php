@extends('admin-modern.layouts.app')

@section('title', 'Edit Travel Partner')

@section('content')

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
