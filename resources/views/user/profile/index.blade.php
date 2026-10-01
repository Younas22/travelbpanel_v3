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
                                <select id="phoneCode" class="border border-gray-200 rounded-lg px-2 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50" style="width: 150px; flex-shrink: 0;">
                                    <option value="">Code</option>
                                    @foreach($countries as $country)
                                        @if($country->dial_code)
                                            <option value="{{ $country->dial_code }}" data-flag="{{ getFlagClass($country->iso2) }}">{{ $country->dial_code }} {{ $country->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                <input type="text" name="phone" id="phoneNumber" placeholder="1234567890"
                                       value="{{ old('phone', $user->phone) }}"
                                       class="flex-1 border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Country</label>
                            <select name="country" id="countrySelect" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50">
                                <option value="">Select Country</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->iso2 }}" data-flag="{{ getFlagClass($country->iso2) }}" {{ ($user->country ?? '') === $country->iso2 ? 'selected' : '' }}>
                                        {{ $country->name }}
                                    </option>
                                @endforeach
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

@push('styles')
<style>
    /* Country / phone-code selects — scoped to these two fields only (the
       layout's own select2 rules are sized for the tiny navbar currency
       switcher and would otherwise squash these to an 11px/34px chip). */
    #countrySelect + .select2-container {
        width: 100% !important;
    }
    /* phoneCode sits next to the phone number input in a flex row — a fixed
       width (not 100%) keeps it from fighting that sibling for space. */
    #phoneCode + .select2-container {
        width: 150px !important;
        flex-shrink: 0;
    }
    #countrySelect + .select2-container .select2-selection--single,
    #phoneCode + .select2-container .select2-selection--single {
        height: 42px !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 0.5rem !important;
        background: #f9fafb !important;
        display: flex;
        align-items: center;
    }
    #countrySelect + .select2-container.select2-container--focus .select2-selection--single,
    #phoneCode + .select2-container.select2-container--focus .select2-selection--single {
        border-color: #60a5fa !important;
        box-shadow: 0 0 0 3px rgba(96, 165, 250, .25);
    }
    #countrySelect + .select2-container .select2-selection__rendered,
    #phoneCode + .select2-container .select2-selection__rendered {
        line-height: 40px !important;
        padding-left: 12px !important;
        padding-right: 28px !important;
        font-size: 0.875rem !important;
        color: #374151 !important;
    }
    /* The default select2 arrow sits inside a cell with its own left
       border, drawn as a tall vertical divider — slim it down to a small
       centered caret instead. */
    #countrySelect + .select2-container .select2-selection__arrow,
    #phoneCode + .select2-container .select2-selection__arrow {
        height: 40px !important;
        right: 6px !important;
        border-left: none !important;
    }
    #countrySelect + .select2-container .select2-selection__arrow b,
    #phoneCode + .select2-container .select2-selection__arrow b {
        border-width: 5px 4px 0 4px !important;
        border-color: #9ca3af transparent transparent transparent !important;
    }
    .select2-dropdown {
        border: 1px solid #e5e7eb !important;
        border-radius: 0.5rem !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, .1);
        overflow: hidden;
    }
    .select2-search--dropdown {
        padding: 8px !important;
    }
    .select2-search--dropdown .select2-search__field {
        border: 1px solid #e5e7eb !important;
        border-radius: 0.375rem !important;
        padding: 6px 10px !important;
        font-size: 0.8125rem !important;
    }
    .select2-results__option {
        padding: 8px 12px !important;
        font-size: 0.8125rem !important;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #0077BE !important;
    }
</style>
@endpush

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

    jQuery('#phoneCode').select2({
        templateResult: formatCountryOption,
        templateSelection: formatPhoneCodeSelection,
        width: '150px',
    });

    // The number is stored as one plain string (no separate dial-code
    // column), so picking a code just prepends it onto the phone field's
    // own value, replacing any leading "+NN " / "+N-NNN " it already had
    // rather than stacking another one on top. Bound via select2's own
    // event (not plain "change") since that's what reliably fires here.
    const phoneNumberInput = document.getElementById('phoneNumber');
    jQuery('#phoneCode').on('select2:select', function (e) {
        const dial = e.params.data.id;
        if (!dial || !phoneNumberInput) return;
        const withoutCode = phoneNumberInput.value.replace(/^\+\d+(-\d+)?\s*/, '').trim();
        phoneNumberInput.value = `${dial} ${withoutCode}`.trim();
    });
}
</script>
@endpush
