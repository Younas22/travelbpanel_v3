@extends('agent.layouts.app')
@section('title', 'My Profile')

@section('content')

<div class="flex items-center gap-3 mb-5">
    <div class="w-9 h-9 rounded-lg flex items-center justify-center ap-tint-bg">
        <i class="fas fa-user-circle ap-accent"></i>
    </div>
    <div>
        <h4 class="text-lg font-bold text-gray-800">My Profile</h4>
        <p class="text-xs text-gray-400">Manage your personal and company information</p>
    </div>
</div>

<div class="flex items-center gap-2 text-xs text-gray-400 mb-4">
    <a href="{{ route('agent.dashboard') }}" class="ap-accent-link">Dashboard</a>
    <i class="fas fa-chevron-right text-gray-300"></i>
    <span class="text-gray-600">My Profile</span>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Left: Forms --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Personal & Company Info --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 font-semibold text-sm text-gray-700">
                Personal & Company Information
            </div>
            <div class="p-5">
                <form method="POST" action="{{ route('agent.profile.update') }}">
                    @csrf
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                First Name <span class="ap-accent">*</span>
                            </label>
                            <input type="text" name="first_name"
                                   class="w-full border rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50 @error('first_name') border-red-400 @else border-gray-200 @enderror"
                                   value="{{ old('first_name', $agent->first_name) }}" required>
                            @error('first_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                Last Name <span class="ap-accent">*</span>
                            </label>
                            <input type="text" name="last_name"
                                   class="w-full border rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50 @error('last_name') border-red-400 @else border-gray-200 @enderror"
                                   value="{{ old('last_name', $agent->last_name) }}" required>
                            @error('last_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Email</label>
                            <input type="email"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm bg-gray-100 text-gray-400 cursor-not-allowed"
                                   value="{{ $agent->email }}" disabled>
                            <p class="text-xs text-gray-400 mt-1">Email cannot be changed. Contact admin.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Phone</label>
                            <input type="text" name="phone"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50"
                                   value="{{ old('phone', $agent->phone) }}" placeholder="+92 300 0000000">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Company Name</label>
                            <input type="text" name="company_name"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50"
                                   value="{{ old('company_name', $agent->company_name) }}" placeholder="Your company name">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Company Phone</label>
                            <input type="text" name="company_phone"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50"
                                   value="{{ old('company_phone', $agent->company_phone) }}" placeholder="+92 21 0000000">
                        </div>

                        <div class="lg:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Company Address</label>
                            <textarea name="company_address" rows="2"
                                      class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50"
                                      placeholder="Street, City, Country">{{ old('company_address', $agent->company_address) }}</textarea>
                        </div>

                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-100 flex justify-end">
                        <button type="submit"
                                class="px-5 py-2.5 rounded-lg text-sm font-semibold text-white flex items-center gap-2 ap-solid-accent-btn">
                            <i class="fas fa-save text-xs"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Change Password --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 font-semibold text-sm text-gray-700">
                Change Password
            </div>
            <div class="p-5">
                <form method="POST" action="{{ route('agent.profile.password') }}">
                    @csrf
                    <div class="space-y-4">

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                Current Password <span class="ap-accent">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" name="current_password" id="cp1"
                                       class="w-full border rounded-lg px-3 py-2.5 pr-10 text-sm focus:outline-none focus:border-blue-400 bg-gray-50 @error('current_password') border-red-400 @else border-gray-200 @enderror"
                                       required>
                                <button type="button" onclick="togglePw('cp1','ci1')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-eye text-sm" id="ci1"></i>
                                </button>
                            </div>
                            @error('current_password')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                    New Password <span class="ap-accent">*</span>
                                </label>
                                <div class="relative">
                                    <input type="password" name="password" id="cp2"
                                           class="w-full border rounded-lg px-3 py-2.5 pr-10 text-sm focus:outline-none focus:border-blue-400 bg-gray-50 @error('password') border-red-400 @else border-gray-200 @enderror"
                                           required>
                                    <button type="button" onclick="togglePw('cp2','ci2')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-eye text-sm" id="ci2"></i>
                                    </button>
                                </div>
                                @error('password')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                    Confirm New Password <span class="ap-accent">*</span>
                                </label>
                                <div class="relative">
                                    <input type="password" name="password_confirmation" id="cp3"
                                           class="w-full border border-gray-200 rounded-lg px-3 py-2.5 pr-10 text-sm focus:outline-none focus:border-blue-400 bg-gray-50"
                                           required>
                                    <button type="button" onclick="togglePw('cp3','ci3')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-eye text-sm" id="ci3"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-100 flex justify-end">
                        <button type="submit"
                                class="px-5 py-2.5 rounded-lg text-sm font-semibold text-white flex items-center gap-2 ap-navy-bg">
                            <i class="fas fa-lock text-xs"></i> Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    {{-- Right: Sidebar --}}
    <div class="lg:col-span-1 space-y-5">

        {{-- Account Info + Profile Photo --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 font-semibold text-sm text-gray-700">
                Account Info
            </div>
            <div class="p-5">

                {{-- Avatar with camera overlay (same as user panel) --}}
                <div class="flex flex-col items-center text-center pb-4 mb-4 border-b border-gray-100">
                    <div class="relative inline-block mb-3">
                        @if($agent->profile_image)
                            <img src="{{ url('public/assets/images/agents/' . $agent->profile_image) }}"
                                 id="avatarPreview"
                                 class="w-20 h-20 rounded-full object-cover border-2 mx-auto ap-accent-border">
                        @else
                            <div class="w-20 h-20 rounded-full flex items-center justify-center text-white text-2xl font-bold mx-auto ap-avatar-gradient"
                                 id="avatarInitials">
                                {{ $agent->initials }}
                            </div>
                            <img src="" id="avatarPreview"
                                 class="w-20 h-20 rounded-full object-cover border-2 mx-auto hidden ap-accent-border">
                        @endif
                        <label for="photoInput"
                               class="absolute bottom-0 right-0 w-7 h-7 rounded-full flex items-center justify-center cursor-pointer shadow-md ap-camera-btn">
                            <i class="fas fa-camera text-white ap-icon-fs-xs"></i>
                        </label>
                    </div>
                    <p class="font-semibold text-gray-800 text-sm">{{ $agent->full_name }}</p>
                    <p class="text-xs text-gray-400">{{ $agent->email }}</p>
                </div>

                {{-- Hidden photo upload form — auto-submits on file select --}}
                <form method="POST" action="{{ route('agent.profile.photo') }}" enctype="multipart/form-data" id="photoForm">
                    @csrf
                    <input type="file" name="photo" id="photoInput" accept="image/*" class="hidden">
                </form>
                @error('photo')<p class="text-xs text-red-500 mb-3">{{ $message }}</p>@enderror

                {{-- Details --}}
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Agent Code</span>
                        <span class="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded font-mono">{{ $agent->agent_code }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Status</span>
                        @if($agent->approval_status === 'active' || $agent->approval_status === 'approved')
                            <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium">{{ ucfirst($agent->approval_status) }}</span>
                        @elseif($agent->approval_status === 'pending')
                            <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">Pending</span>
                        @elseif($agent->approval_status === 'suspended')
                            <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs font-medium">Suspended</span>
                        @else
                            <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full text-xs font-medium">{{ ucfirst($agent->approval_status) }}</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Commission</span>
                        <span class="text-xs font-semibold text-gray-700">{{ $agent->commission_rate ?? 0 }}%</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Member Since</span>
                        <span class="text-xs text-gray-700">{{ $agent->created_at->format('M Y') }}</span>
                    </div>
                </div>

            </div>
        </div>

        {{-- Company Logo --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 font-semibold text-sm text-gray-700">
                Company Logo
            </div>
            <div class="p-5">
                @if($agent->company_logo)
                <div class="flex justify-center mb-4">
                    <img src="{{ url('public/assets/images/settings/branding/' . $agent->company_logo) }}"
                         alt="Logo" class="max-h-16 rounded object-contain">
                </div>
                @endif
                <form method="POST" action="{{ route('agent.profile.logo') }}" enctype="multipart/form-data" id="logoForm">
                    @csrf
                    <label for="logoInput"
                           class="flex flex-col items-center justify-center border-2 border-dashed border-gray-200 rounded-xl py-5 px-4 cursor-pointer hover:border-blue-300 transition mb-3">
                        <i class="fas fa-cloud-upload-alt text-2xl mb-2 ap-accent"></i>
                        <span class="text-xs font-semibold text-gray-600">Click to upload logo</span>
                        <span class="text-xs text-gray-400 mt-0.5">JPG, PNG — max 2MB</span>
                        <input type="file" name="logo" id="logoInput" class="hidden" accept="image/*">
                    </label>
                    @error('logo')<p class="text-xs text-red-500 mb-2">{{ $message }}</p>@enderror
                    <button type="submit"
                            class="w-full px-5 py-2.5 rounded-lg text-sm font-semibold text-white flex items-center justify-center gap-2 ap-solid-accent-btn">
                        <i class="fas fa-upload text-xs"></i> Upload Logo
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
        preview.classList.remove('hidden');
        if (initials) initials.classList.add('hidden');
    };
    reader.readAsDataURL(this.files[0]);
    document.getElementById('photoForm').submit();
});

function togglePw(fid, iid) {
    const f = document.getElementById(fid), i = document.getElementById(iid);
    f.type = f.type === 'password' ? 'text' : 'password';
    i.className = f.type === 'password' ? 'fas fa-eye text-sm' : 'fas fa-eye-slash text-sm';
}
</script>
@endpush
