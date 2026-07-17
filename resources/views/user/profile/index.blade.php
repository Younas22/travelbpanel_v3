@extends('user.layouts.app')
@section('title', 'Profile')

@section('content')

{{-- Breadcrumb --}}
<div class="flex items-center gap-2 text-xs text-gray-400 mb-4">
    <a href="{{ route('user.dashboard') }}" style="color:#0077BE; text-decoration:none;">Dashboard</a>
    <i class="fas fa-chevron-right text-gray-300"></i>
    <span class="text-gray-600">Profile</span>
</div>

{{-- Page Header --}}
<div class="flex items-center gap-3 mb-5">
    <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background:#e8f4fd;">
        <i class="fas fa-user" style="color:#0077BE;"></i>
    </div>
    <div>
        <h4 class="text-lg font-bold text-gray-800">Profile</h4>
        <p class="text-xs text-gray-400">Manage Your Profile</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Left: Forms --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Personal Info --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 font-semibold text-sm text-gray-700">
                Personal Information
            </div>
            <div class="p-5">
                <form method="POST" action="{{ route('user.profile.update') }}">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Title</label>
                            <select name="title" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50">
                                <option value="Mr" {{ ($user->title ?? '') === 'Mr' ? 'selected' : '' }}>Mr</option>
                                <option value="Mrs" {{ ($user->title ?? '') === 'Mrs' ? 'selected' : '' }}>Mrs</option>
                                <option value="Ms" {{ ($user->title ?? '') === 'Ms' ? 'selected' : '' }}>Ms</option>
                                <option value="Dr" {{ ($user->title ?? '') === 'Dr' ? 'selected' : '' }}>Dr</option>
                            </select>
                        </div>

                        <div class="sm:col-span-1"></div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                First Name <span style="color:#0077BE;">*</span>
                            </label>
                            <input type="text" name="first_name" required
                                   value="{{ old('first_name', $user->first_name) }}"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50 @error('first_name') border-red-400 @enderror">
                            @error('first_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                Last Name <span style="color:#0077BE;">*</span>
                            </label>
                            <input type="text" name="last_name" required
                                   value="{{ old('last_name', $user->last_name) }}"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50 @error('last_name') border-red-400 @enderror">
                            @error('last_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                Email Address <span style="color:#0077BE;">*</span>
                            </label>
                            <input type="email" disabled value="{{ $user->email }}"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm bg-gray-100 text-gray-500 cursor-not-allowed">
                            <p class="text-xs text-gray-400 mt-1">Email cannot be changed. Contact support.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Phone Number</label>
                            <div class="flex gap-2">
                                <select class="border border-gray-200 rounded-lg px-2 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50 w-24">
                                    <option>Code</option>
                                    <option>+92</option>
                                    <option>+1</option>
                                    <option>+44</option>
                                </select>
                                <input type="text" name="phone" placeholder="1234567890"
                                       value="{{ old('phone', $user->phone) }}"
                                       class="flex-1 border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Country</label>
                            <select name="country" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50">
                                <option value="">Select Country</option>
                                <option value="PK" {{ ($user->country ?? '') === 'PK' ? 'selected' : '' }}>Pakistan</option>
                                <option value="US" {{ ($user->country ?? '') === 'US' ? 'selected' : '' }}>United States</option>
                                <option value="GB" {{ ($user->country ?? '') === 'GB' ? 'selected' : '' }}>United Kingdom</option>
                                <option value="AE" {{ ($user->country ?? '') === 'AE' ? 'selected' : '' }}>UAE</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">State</label>
                            <input type="text" name="state"
                                   value="{{ old('state', $user->state) }}"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">PO Box</label>
                            <input type="text" name="zip_code"
                                   value="{{ old('zip_code', $user->zip_code) }}"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Address</label>
                            <textarea name="address" rows="2"
                                      class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50 resize-none">{{ old('address', $user->address) }}</textarea>
                        </div>

                    </div>

                    <div class="mt-5 pt-4 border-t border-gray-100 flex justify-end">
                        <button type="submit"
                                class="px-5 py-2.5 rounded-lg text-sm font-semibold text-white"
                                style="background:#0077BE; border:none; cursor:pointer;">
                            Update Profile
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
                <form method="POST" action="{{ route('user.profile.password') }}">
                    @csrf
                    <div class="space-y-4">

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                Current Password <span style="color:#0077BE;">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" name="current_password" id="cp1" required
                                       class="w-full border border-gray-200 rounded-lg px-3 py-2.5 pr-10 text-sm focus:outline-none focus:border-blue-400 bg-gray-50 @error('current_password') border-red-400 @enderror">
                                <button type="button" onclick="togglePw('cp1','ci1')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-eye text-sm" id="ci1"></i>
                                </button>
                            </div>
                            @error('current_password')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                New Password <span style="color:#0077BE;">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" name="password" id="cp2" required
                                       class="w-full border border-gray-200 rounded-lg px-3 py-2.5 pr-10 text-sm focus:outline-none focus:border-blue-400 bg-gray-50 @error('password') border-red-400 @enderror">
                                <button type="button" onclick="togglePw('cp2','ci2')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-eye text-sm" id="ci2"></i>
                                </button>
                            </div>
                            @error('password')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                Confirm Password <span style="color:#0077BE;">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" name="password_confirmation" id="cp3" required
                                       class="w-full border border-gray-200 rounded-lg px-3 py-2.5 pr-10 text-sm focus:outline-none focus:border-blue-400 bg-gray-50">
                                <button type="button" onclick="togglePw('cp3','ci3')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-eye text-sm" id="ci3"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 pt-4 border-t border-gray-100 flex justify-end">
                        <button type="submit"
                                class="px-5 py-2.5 rounded-lg text-sm font-semibold text-white"
                                style="background:#0077BE; border:none; cursor:pointer;">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    {{-- Right: Avatar Card --}}
    <div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 text-center sticky top-4">

            {{-- Avatar --}}
            <div class="relative inline-block mb-3">
                @if($user->profile_image)
                    <img src="{{ url('public/assets/images/avatars/' . $user->profile_image) }}" id="avatarPreview"
                         class="w-20 h-20 rounded-full object-cover border-2 mx-auto" style="border-color:#0077BE;">
                @else
                    <div class="w-20 h-20 rounded-full flex items-center justify-center text-white text-2xl font-bold mx-auto"
                         id="avatarInitials"
                         style="background:linear-gradient(135deg,#0077BE,#005a8f);">
                        {{ strtoupper(substr($user->first_name,0,1).substr($user->last_name,0,1)) }}
                    </div>
                    <img src="" id="avatarPreview" class="w-20 h-20 rounded-full object-cover border-2 mx-auto hidden" style="border-color:#0077BE;">
                @endif
                {{-- Camera overlay --}}
                <label for="avatarInput"
                       class="absolute bottom-0 right-0 w-7 h-7 rounded-full flex items-center justify-center cursor-pointer shadow-md"
                       style="background:#0077BE; border:2px solid #fff;">
                    <i class="fas fa-camera text-white" style="font-size:.65rem;"></i>
                </label>
            </div>

            <p class="font-bold text-gray-800">{{ $user->first_name }} {{ $user->last_name }}</p>
            <p class="text-xs text-gray-400 mt-0.5 mb-4">{{ $user->email }}</p>
            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1 rounded-full" style="background:#e8f4fd; color:#0077BE;">
                <i class="fas fa-user"></i> User Account
            </span>

            {{-- Hidden upload form --}}
            <form method="POST" action="{{ route('user.profile.avatar') }}" enctype="multipart/form-data" id="avatarForm">
                @csrf
                <input type="file" id="avatarInput" name="avatar" accept="image/*" class="hidden">
            </form>

            @error('avatar')
                <p class="text-xs text-red-500 mt-2">{{ $message }}</p>
            @enderror

            <div class="mt-4 pt-4 border-t border-gray-100 text-xs text-gray-400">
                Member since {{ $user->created_at->format('M Y') }}
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
// Avatar instant preview + auto-submit
document.getElementById('avatarInput').addEventListener('change', function() {
    if (!this.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        const preview = document.getElementById('avatarPreview');
        const initials = document.getElementById('avatarInitials');
        preview.src = e.target.result;
        preview.classList.remove('hidden');
        if (initials) initials.classList.add('hidden');
    };
    reader.readAsDataURL(this.files[0]);
    document.getElementById('avatarForm').submit();
});

function togglePw(fid, iid) {
    const f = document.getElementById(fid), i = document.getElementById(iid);
    f.type = f.type === 'password' ? 'text' : 'password';
    i.className = f.type === 'password' ? 'fas fa-eye text-sm' : 'fas fa-eye-slash text-sm';
}
</script>
@endpush
