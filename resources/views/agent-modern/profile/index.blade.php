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
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $agent->phone) }}" placeholder="+92 300 0000000">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Company Name</label>
                                <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $agent->company_name) }}" placeholder="Your company name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Company Phone</label>
                                <input type="text" name="company_phone" class="form-control" value="{{ old('company_phone', $agent->company_phone) }}" placeholder="+92 21 0000000">
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
</script>
@endpush
