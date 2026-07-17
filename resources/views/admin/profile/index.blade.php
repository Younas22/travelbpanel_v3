@extends('admin.layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="content-area p-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-1">My Profile</h2>
                <p class="text-muted mb-0">Manage your profile information and settings</p>
            </div>
        </div>
    </div>

    <!-- Profile Container -->
    <div class="settings-container">
        <!-- Profile Header with Photo -->
        <div class="settings-header bg-gradient-primary text-white p-4 rounded">
            <div class="row align-items-center">
                <div class="col-md-2 text-center">
                    <div class="profile-image-container position-relative d-inline-block">
                        <img src="{{ url('public/'.$user->profile_image) }}"
                             alt="Profile"
                             class="rounded-circle border border-4 border-white shadow"
                             width="120" height="120"
                             style="object-fit: cover;">
                        <button type="button"
                                class="btn btn-sm btn-light rounded-circle position-absolute bottom-0 end-0 shadow"
                                data-bs-toggle="modal"
                                data-bs-target="#profilePictureModal"
                                style="width: 35px; height: 35px;">
                            <i class="bi bi-camera"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-10">
                    <h3 class="mb-1">{{ $user->full_name }}</h3>
                    <p class="mb-1"><i class="bi bi-envelope me-2"></i>{{ $user->email }}</p>
                    <p class="mb-0"><i class="bi bi-shield-check me-2"></i>{{ $user->getUserTypeLabel() }}</p>
                </div>
            </div>
        </div>

        <!-- Settings Tabs -->
        <ul class="nav nav-tabs settings-tabs mt-4" id="profileTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link modern-btn active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab">
                    <i class="bi bi-person me-2"></i>Personal Information
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link modern-btn" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                    <i class="bi bi-lock me-2"></i>Security
                </button>
            </li>
            <li class="nav-item d-none" role="presentation">
                <button class="nav-link modern-btn" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications" type="button" role="tab">
                    <i class="bi bi-bell me-2"></i>Notifications
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="profileTabContent">
            <!-- Personal Information Tab -->
            <div class="tab-pane fade show active" id="personal" role="tabpanel">
                <div class="settings-content">
                    <form action="{{ route('admin.profile.update') }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="settings-section">
                            <div class="section-title">Basic Information</div>
                            <div class="section-description">Update your basic profile information</div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">First Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                               name="first_name" value="{{ old('first_name', $user->first_name) }}" required>
                                        @error('first_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                               name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
                                        @error('last_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                               name="email" value="{{ old('email', $user->email) }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Phone Number</label>
                                        <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                               name="phone" value="{{ old('phone', $user->phone) }}">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Date of Birth</label>
                                        <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror"
                                               name="date_of_birth" value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}">
                                        @error('date_of_birth')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Gender</label>
                                        <select class="form-select @error('gender') is-invalid @enderror" name="gender">
                                            <option value="">Select Gender</option>
                                            <option value="male" {{ old('gender', $user->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                            <option value="female" {{ old('gender', $user->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                            <option value="other" {{ old('gender', $user->gender) == 'other' ? 'selected' : '' }}>Other</option>
                                            <option value="prefer-not-to-say" {{ old('gender', $user->gender) == 'prefer-not-to-say' ? 'selected' : '' }}>Prefer not to say</option>
                                        </select>
                                        @error('gender')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($user->isAdmin() || $user->isAgent())
                        <div class="settings-section d-none">
                            <div class="section-title">Work Information</div>
                            <div class="section-description">Your employment details</div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Employee ID</label>
                                        <input type="text" class="form-control @error('employee_id') is-invalid @enderror"
                                               name="employee_id" value="{{ old('employee_id', $user->employee_id) }}">
                                        @error('employee_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Department</label>
                                        <input type="text" class="form-control @error('department') is-invalid @enderror"
                                               name="department" value="{{ old('department', $user->department) }}">
                                        @error('department')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="settings-section d-none">
                            <div class="section-title">Address Information</div>
                            <div class="section-description">Your contact address</div>

                            <div class="form-group">
                                <label class="form-label">Street Address</label>
                                <textarea class="form-control @error('address') is-invalid @enderror"
                                          name="address" rows="2">{{ old('address', $user->address) }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">City</label>
                                        <input type="text" class="form-control @error('city') is-invalid @enderror"
                                               name="city" value="{{ old('city', $user->city) }}">
                                        @error('city')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">State/Province</label>
                                        <input type="text" class="form-control @error('state') is-invalid @enderror"
                                               name="state" value="{{ old('state', $user->state) }}">
                                        @error('state')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">ZIP/Postal Code</label>
                                        <input type="text" class="form-control @error('zip_code') is-invalid @enderror"
                                               name="zip_code" value="{{ old('zip_code', $user->zip_code) }}">
                                        @error('zip_code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Country</label>
                                        <input type="text" class="form-control @error('country') is-invalid @enderror"
                                               name="country" value="{{ old('country', $user->country) }}">
                                        @error('country')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-success modern-btn">
                                <i class="bi bi-check-lg me-2"></i>Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Security Tab -->
            <div class="tab-pane fade" id="security" role="tabpanel">
                <div class="settings-content">
                    <form action="{{ route('admin.profile.update-password') }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="settings-section">
                            <div class="section-title">Change Password</div>
                            <div class="section-description">Update your account password</div>

                            <div class="form-group">
                                <label class="form-label">Current Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                                       name="current_password" required>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">New Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                       name="password" required>
                                <small class="text-muted">Must be at least 8 characters long</small>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control"
                                       name="password_confirmation" required>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-success modern-btn">
                                <i class="bi bi-shield-check me-2"></i>Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Notifications Tab -->
            <div class="tab-pane fade" id="notifications" role="tabpanel">
                <div class="settings-content">
                    <form action="{{ route('admin.profile.update-notifications') }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="settings-section">
                            <div class="section-title">Notification Preferences</div>
                            <div class="section-description">Choose how you want to receive notifications</div>

                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="email_notifications"
                                       name="email_notifications" {{ $user->email_notifications ? 'checked' : '' }}>
                                <label class="form-check-label" for="email_notifications">
                                    <strong>Email Notifications</strong>
                                    <br><small class="text-muted">Receive notifications via email</small>
                                </label>
                            </div>

                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="sms_notifications"
                                       name="sms_notifications" {{ $user->sms_notifications ? 'checked' : '' }}>
                                <label class="form-check-label" for="sms_notifications">
                                    <strong>SMS Notifications</strong>
                                    <br><small class="text-muted">Receive notifications via SMS</small>
                                </label>
                            </div>

                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="marketing_emails"
                                       name="marketing_emails" {{ $user->marketing_emails ? 'checked' : '' }}>
                                <label class="form-check-label" for="marketing_emails">
                                    <strong>Marketing Emails</strong>
                                    <br><small class="text-muted">Receive promotional emails and updates</small>
                                </label>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-success modern-btn">
                                <i class="bi bi-check-lg me-2"></i>Update Preferences
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Profile Picture Modal -->
<div class="modal fade" id="profilePictureModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Profile Picture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.profile.update-picture') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <img src="{{ url('public/'.$user->profile_image) }}"
                             alt="Current Profile"
                             class="rounded-circle border shadow mb-3"
                             width="150" height="150"
                             style="object-fit: cover;"
                             id="previewImage">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Choose New Picture</label>
                        <input type="file" class="form-control @error('profile_image') is-invalid @enderror"
                               name="profile_image" accept="image/*" id="profileImageInput" required>
                        <small class="text-muted">Supported formats: JPG, PNG, GIF. Max size: 2MB</small>
                        @error('profile_image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    @if($user->profile_image)
                    <button type="button" class="btn btn-danger me-auto" onclick="removeProfilePicture()">
                        <i class="bi bi-trash"></i> Remove Picture
                    </button>
                    @endif
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Remove Profile Picture Form -->
@if($user->profile_image)
<form id="removeProfilePictureForm" action="{{ route('admin.profile.remove-picture') }}" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>
@endif

@endsection

@push('scripts')
<script>
// Preview image before upload
document.getElementById('profileImageInput')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImage').src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
});

// Remove profile picture
function removeProfilePicture() {
    if (confirm('Are you sure you want to remove your profile picture?')) {
        document.getElementById('removeProfilePictureForm').submit();
    }
}
</script>
@endpush
