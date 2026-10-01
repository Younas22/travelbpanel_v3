@extends('agent-modern.layouts.app')
@section('title', 'My Profile')

@section('content')

    <div class="ap-breadcrumb">
        <a href="{{ route('agent.dashboard') }}">Dashboard</a>
        <i class="bi bi-chevron-right"></i>
        <span>My Profile</span>
    </div>

    <div class="ap-page-header">
        <div class="ap-page-header-left">
            <div class="ap-icon-badge"><i class="bi bi-person-circle"></i></div>
            <div>
                <h4 class="ap-page-title">My Profile</h4>
                <p class="ap-page-sub">Manage your personal and company information</p>
            </div>
        </div>
    </div>

    <div class="row g-4">

        <div class="col-lg-8">

            <div class="am-card mb-4">
                <div class="am-card-header">Personal &amp; Company Information</div>
                <div class="am-card-body">
                    <form method="POST" action="{{ route('agent.profile.update') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name <span style="color: var(--danger-color);">*</span></label>
                                <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name', $agent->first_name) }}" required>
                                @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name <span style="color: var(--danger-color);">*</span></label>
                                <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name', $agent->last_name) }}" required>
                                @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="{{ $agent->email }}" disabled style="cursor: not-allowed; opacity: .6;">
                                <div class="form-text">Email cannot be changed. Contact admin.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <div class="phone-input-group d-flex align-items-stretch">
                                    <select id="phoneCode" class="phone-code-select" data-target="phoneNumber">
                                        <option value="">Code</option>
                                        @foreach($countries as $country)
                                            @if($country->dial_code)
                                                <option value="{{ $country->dial_code }}" data-flag="{{ getFlagClass($country->iso2) }}">{{ $country->dial_code }} {{ $country->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <input type="text" name="phone" id="phoneNumber" class="form-control flex-fill" style="border-left: none; border-top-left-radius: 0; border-bottom-left-radius: 0;" value="{{ old('phone', $agent->phone) }}" placeholder="300 0000000">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Country</label>
                                <select name="country" id="countrySelect" class="form-select">
                                    <option value="">Select Country</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->iso2 }}" data-flag="{{ getFlagClass($country->iso2) }}" {{ ($agent->country ?? '') === $country->iso2 ? 'selected' : '' }}>
                                            {{ $country->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Company Name</label>
                                <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $agent->company_name) }}" placeholder="Your company name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Company Phone</label>
                                <div class="phone-input-group d-flex align-items-stretch">
                                    <select id="companyPhoneCode" class="phone-code-select" data-target="companyPhoneNumber">
                                        <option value="">Code</option>
                                        @foreach($countries as $country)
                                            @if($country->dial_code)
                                                <option value="{{ $country->dial_code }}" data-flag="{{ getFlagClass($country->iso2) }}">{{ $country->dial_code }} {{ $country->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <input type="text" name="company_phone" id="companyPhoneNumber" class="form-control flex-fill" style="border-left: none; border-top-left-radius: 0; border-bottom-left-radius: 0;" value="{{ old('company_phone', $agent->company_phone) }}" placeholder="21 0000000">
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Company Address</label>
                                <textarea name="company_address" rows="2" class="form-control" placeholder="Street, City, Country">{{ old('company_address', $agent->company_address) }}</textarea>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-4 pt-3" style="border-top: 1px solid var(--border-color);">
                            <button type="submit" class="ap-btn-primary">
                                <i class="bi bi-save"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="am-card">
                <div class="am-card-header">Change Password</div>
                <div class="am-card-body">
                    <form method="POST" action="{{ route('agent.profile.password') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Current Password <span style="color: var(--danger-color);">*</span></label>
                            <div class="auth-input-group">
                                <input type="password" name="current_password" id="cp1" class="form-control @error('current_password') is-invalid @enderror" required>
                                <button type="button" class="auth-eye" onclick="apTogglePw('cp1','ci1')"><i class="bi bi-eye" id="ci1"></i></button>
                            </div>
                            @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">New Password <span style="color: var(--danger-color);">*</span></label>
                                <div class="auth-input-group">
                                    <input type="password" name="password" id="cp2" class="form-control @error('password') is-invalid @enderror" required>
                                    <button type="button" class="auth-eye" onclick="apTogglePw('cp2','ci2')"><i class="bi bi-eye" id="ci2"></i></button>
                                </div>
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm New Password <span style="color: var(--danger-color);">*</span></label>
                                <div class="auth-input-group">
                                    <input type="password" name="password_confirmation" id="cp3" class="form-control" required>
                                    <button type="button" class="auth-eye" onclick="apTogglePw('cp3','ci3')"><i class="bi bi-eye" id="ci3"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-4 pt-3" style="border-top: 1px solid var(--border-color);">
                            <button type="submit" class="ap-btn-secondary">
                                <i class="bi bi-lock"></i> Change Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

        <div class="col-lg-4">

            <div class="am-card mb-4">
                <div class="am-card-header">Account Info</div>
                <div class="am-card-body">

                    <div class="ap-profile-header">
                        <div class="ap-avatar-wrap">
                            @if($agent->profile_image)
                                <img src="{{ url('public/assets/images/agents/' . $agent->profile_image) }}" id="avatarPreview" class="ap-avatar-lg">
                            @else
                                <div class="ap-avatar-lg" id="avatarInitials">{{ $agent->initials }}</div>
                                <img src="" id="avatarPreview" class="ap-avatar-lg d-none">
                            @endif
                            <label for="photoInput" class="ap-avatar-camera-btn">
                                <i class="bi bi-camera"></i>
                            </label>
                        </div>
                        <div class="ap-profile-name">{{ $agent->full_name }}</div>
                        <div class="ap-profile-email">{{ $agent->email }}</div>
                    </div>

                    <form method="POST" action="{{ route('agent.profile.photo') }}" enctype="multipart/form-data" id="photoForm">
                        @csrf
                        <input type="file" name="photo" id="photoInput" accept="image/*" class="d-none">
                    </form>
                    @error('photo') <div class="invalid-feedback d-block mb-3">{{ $message }}</div> @enderror

                    <div class="ap-kv-row">
                        <span class="label">Agent Code</span>
                        <code style="font-size: 11px; background: color-mix(in srgb, var(--text-color) 8%, transparent); padding: 2px 8px; border-radius: 4px;">{{ $agent->agent_code }}</code>
                    </div>
                    <div class="ap-kv-row">
                        <span class="label">Status</span>
                        @if($agent->approval_status === 'active' || $agent->approval_status === 'approved')
                            <span class="badge bg-success">{{ ucfirst($agent->approval_status) }}</span>
                        @elseif($agent->approval_status === 'pending')
                            <span class="badge bg-warning">Pending</span>
                        @elseif($agent->approval_status === 'suspended')
                            <span class="badge bg-danger">Suspended</span>
                        @else
                            <span class="badge bg-secondary">{{ ucfirst($agent->approval_status) }}</span>
                        @endif
                    </div>
                    <div class="ap-kv-row"><span class="label">Commission</span><span style="font-weight: 650;">{{ $agent->commission_rate ?? 0 }}%</span></div>
                    <div class="ap-kv-row"><span class="label">Member Since</span><span>{{ $agent->created_at->format('M Y') }}</span></div>
                </div>
            </div>

            <div class="am-card">
                <div class="am-card-header">Company Logo</div>
                <div class="am-card-body">
                    @if($agent->company_logo)
                    <div class="text-center mb-3">
                        <img src="{{ url('public/assets/images/settings/branding/' . $agent->company_logo) }}" alt="Logo" style="max-height: 64px; border-radius: var(--radius-md); object-fit: contain;">
                    </div>
                    @endif
                    <form method="POST" action="{{ route('agent.profile.logo') }}" enctype="multipart/form-data" id="logoForm">
                        @csrf
                        <label for="logoInput" class="ap-upload-dropzone mb-3" style="padding: var(--am-space-4);">
                            <i class="bi bi-cloud-arrow-up"></i>
                            <span class="ap-upload-title">Click to upload logo</span>
                            <span class="ap-upload-hint">JPG, PNG — max 2MB</span>
                            <input type="file" name="logo" id="logoInput" class="d-none" accept="image/*">
                        </label>
                        @error('logo') <div class="invalid-feedback d-block mb-2">{{ $message }}</div> @enderror
                        <button type="submit" class="ap-btn-primary w-100 justify-content-center">
                            <i class="bi bi-upload"></i> Upload Logo
                        </button>
                    </form>
                </div>
            </div>

        </div>

    </div>

@endsection

@push('styles')
<style>
    /* Country / phone-code selects — select2 ships with no styling of its
       own beyond the bare library default, so these match the theme's own
       .form-control/.form-select look (same CSS variables) instead of
       looking like a separate, unstyled widget. */
    #countrySelect + .select2-container {
        width: 100% !important;
    }
    /* phoneCode/companyPhoneCode sit directly against their phone number
       input (its own right border is the only divider between them, like
       a Bootstrap input-group) — narrow, and square on the side touching
       the input. Shared by both the personal Phone and Company Phone
       fields. */
    .phone-code-select + .select2-container {
        width: 92px !important;
        flex-shrink: 0;
    }
    #countrySelect + .select2-container .select2-selection--single,
    .phone-code-select + .select2-container .select2-selection--single {
        height: calc(1.5em + 18px + 2px) !important;
        border: 1px solid var(--input-border) !important;
        border-radius: var(--input-radius) !important;
        background: var(--am-input-fill) !important;
        display: flex;
        align-items: center;
    }
    .phone-code-select + .select2-container .select2-selection--single {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
        border-right: none !important;
    }
    #countrySelect + .select2-container.select2-container--focus .select2-selection--single,
    .phone-code-select + .select2-container.select2-container--focus .select2-selection--single {
        border-color: var(--input-focus-color) !important;
        background: var(--card-bg) !important;
        box-shadow: var(--am-shadow-focus);
        position: relative;
        z-index: 1;
    }
    #countrySelect + .select2-container .select2-selection__rendered,
    .phone-code-select + .select2-container .select2-selection__rendered {
        line-height: 1.5 !important;
        padding-left: 12px !important;
        padding-right: 22px !important;
        font-size: 13.5px !important;
        color: var(--text-color) !important;
    }
    /* The default select2 arrow sits inside a cell with its own left
       border, drawn as a tall vertical divider — slim it down to a small
       centered caret instead. */
    #countrySelect + .select2-container .select2-selection__arrow,
    .phone-code-select + .select2-container .select2-selection__arrow {
        height: 100% !important;
        right: 4px !important;
        border-left: none !important;
    }
    #countrySelect + .select2-container .select2-selection__arrow b,
    .phone-code-select + .select2-container .select2-selection__arrow b {
        border-width: 5px 4px 0 4px !important;
        border-color: color-mix(in srgb, var(--text-color) 45%, transparent) transparent transparent transparent !important;
    }
    /* Each phone-code select's own box is a narrow 92px — the dropdown
       list needs real width so flag + code + country name sit on one
       line, not wrapped across several. */
    .phone-code-dropdown {
        min-width: 240px !important;
    }
    .select2-dropdown {
        border: 1px solid var(--input-border) !important;
        border-radius: var(--input-radius) !important;
        box-shadow: var(--am-shadow-md, 0 10px 25px -5px rgba(0, 0, 0, .15));
        overflow: hidden;
    }
    .select2-search--dropdown {
        padding: 8px !important;
        background: var(--card-bg) !important;
    }
    .select2-search--dropdown .select2-search__field {
        border: 1px solid var(--input-border) !important;
        border-radius: calc(var(--input-radius) - 2px) !important;
        padding: 6px 10px !important;
        font-size: 13px !important;
        background: var(--am-input-fill) !important;
        color: var(--text-color) !important;
    }
    .select2-results {
        background: var(--card-bg) !important;
    }
    .select2-results__option {
        padding: 8px 12px !important;
        font-size: 13px !important;
        color: var(--text-color) !important;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: var(--primary-color) !important;
        color: #fff !important;
    }
</style>
@endpush

@push('scripts')
<script>
document.getElementById('photoInput').addEventListener('change', function () {
    if (!this.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        const preview  = document.getElementById('avatarPreview');
        const initials = document.getElementById('avatarInitials');
        preview.src = e.target.result;
        preview.classList.remove('d-none');
        if (initials) initials.classList.add('d-none');
    };
    reader.readAsDataURL(this.files[0]);
    document.getElementById('photoForm').submit();
});

function apTogglePw(fid, iid) {
    const f = document.getElementById(fid), i = document.getElementById(iid);
    f.type = f.type === 'password' ? 'text' : 'password';
    i.className = f.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}

// Country + phone-code dropdowns: searchable, with each option's flag
// (same templateResult/templateSelection pattern as the site's currency
// switcher — flag class comes from each <option data-flag="...">).
if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
    function formatCountryOption(state) {
        if (!state.id) return state.text;
        const flagClass = jQuery(state.element).data('flag');
        if (!flagClass) return state.text;
        return jQuery('<span style="display: inline-flex; align-items: center;"><span class="' + flagClass + '" style="margin-right: 8px; flex-shrink: 0;"></span>' + state.text + '</span>');
    }

    // The list (while searching) still shows "+92 Pakistan" so multiple
    // countries sharing one dial code are easy to tell apart — but once
    // picked, the closed field only needs the code itself, not the name.
    function formatPhoneCodeSelection(state) {
        if (!state.id) return state.text;
        const flagClass = jQuery(state.element).data('flag');
        if (!flagClass) return state.id;
        return jQuery('<span style="display: inline-flex; align-items: center;"><span class="' + flagClass + '" style="margin-right: 6px; flex-shrink: 0;"></span>' + state.id + '</span>');
    }

    jQuery('#countrySelect').select2({
        templateResult: formatCountryOption,
        templateSelection: formatCountryOption,
        width: '100%',
    });

    // Shared by both the personal Phone and Company Phone code pickers.
    jQuery('.phone-code-select').select2({
        templateResult: formatCountryOption,
        templateSelection: formatPhoneCodeSelection,
        width: '92px',
        // The select itself is a narrow 92px (just the code) — without this
        // the dropdown list inherits that same width and wraps every
        // country name onto several lines instead of showing it properly.
        dropdownAutoWidth: true,
        dropdownCssClass: 'phone-code-dropdown',
    });

    // The number is stored as one plain string (no separate dial-code
    // column), so picking a code just prepends it onto its own phone
    // field's value (named via data-target), replacing any leading
    // "+NN " / "+N-NNN " it already had rather than stacking another one
    // on top. Bound via select2's own event (not plain "change") since
    // that's what reliably fires here.
    jQuery('.phone-code-select').on('select2:select', function (e) {
        const dial = e.params.data.id;
        const target = document.getElementById(jQuery(this).data('target'));
        if (!dial || !target) return;
        const withoutCode = target.value.replace(/^\+\d+(-\d+)?\s*/, '').trim();
        target.value = `${dial} ${withoutCode}`.trim();
    });
}
</script>
@endpush
