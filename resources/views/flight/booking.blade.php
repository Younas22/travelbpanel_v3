@extends('common.layout')
@section('content')

<div class="max-w-7xl mx-auto px-4 py-8">
    @if(session('error'))
    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg flex items-center gap-3">
        <i class="fas fa-exclamation-circle text-red-500"></i>
        {{ session('error') }}
    </div>
    @endif
    <form method="post" id="MSF-multiStepForm" action="{{ route('flight_booking') }}" class="flex flex-col lg:flex-row gap-6">
        @csrf
        <input type="hidden" name="flight_segment" value="{{ encrypt(json_encode($routes))}}">
        <input type="hidden" name="booking_data" value="{{ encrypt(json_encode($booking_data)) }}">
        <input type="hidden" name="currency" value="{{ encrypt($routes->segments[0][0]->currency)}}">
        <input type="hidden" name="price" value="{{ encrypt($routes->segments[0][0]->price)}}">
        <input type="hidden" name="partner_name" value="{{ encrypt($routes->segments[0][0]->supplier)}}">

        <!-- Left Section -->
        <div class="flex-1 space-y-6">
            {{-- Personal Information is the booker's own contact details.
                 An agent already has these on their account and is booking
                 on behalf of a traveller, not themselves, so this section is
                 skipped for them — FlightsController::flight_booking() fills
                 it in from the agent's own profile instead. A logged-in
                 customer isn't skipped (they still see the confirmation),
                 but the fields are pre-filled from their own account and
                 locked read-only rather than asking them to re-type it on
                 every booking — only a true guest (not logged in) types
                 this in manually. --}}
            @php
                $loggedInCustomer = (auth()->check() && auth()->user()->isCustomer()) ? auth()->user() : null;
                $lockedFieldClass = 'w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition'
                    . ($loggedInCustomer ? ' bg-gray-100 text-gray-600 cursor-not-allowed' : '');
                $matchedCountry = null;
                if ($loggedInCustomer && $loggedInCustomer->country) {
                    $matchedCountry = $countries->first(fn($c) => strcasecmp($c->country, $loggedInCustomer->country) === 0
                        || strcasecmp($c->country_code, $loggedInCustomer->country) === 0);
                }
            @endphp
            @unless(auth()->check() && auth()->user()->isAgent())
            <!-- Personal Information -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-1 flex items-center">
                    <i class="fas fa-user-circle text-blue-600 mr-3" style="color: #0077BE;"></i>
                    {{t('flightbooking.personal_information')}}
                </h2>
                @if($loggedInCustomer)
                    <p class="text-xs text-gray-500 mb-5">Using your account details. To change these, update your <a href="{{ route('user.profile.index') }}" class="text-blue-600 underline">profile</a>.</p>
                @else
                    <div class="mb-6"></div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{t('flightbooking.first_name')}} *</label>
                        <input type="text" class="{{ $lockedFieldClass }}" name="user[first_name]" placeholder="{{t('flightbooking.enter_first_name')}}" value="{{ old('user.first_name', $loggedInCustomer->first_name ?? '') }}" @if($loggedInCustomer) readonly @else required @endif>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{t('flightbooking.last_name')}} *</label>
                        <input type="text" class="{{ $lockedFieldClass }}" name="user[last_name]" placeholder="{{t('flightbooking.enter_last_name')}}" value="{{ old('user.last_name', $loggedInCustomer->last_name ?? '') }}" @if($loggedInCustomer) readonly @else required @endif>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{t('flightbooking.email')}} *</label>
                        <input type="email" class="{{ $lockedFieldClass }}" name="user[email]" placeholder="example@gmail.com" value="{{ old('user.email', $loggedInCustomer->email ?? '') }}" @if($loggedInCustomer) readonly @else required @endif>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{t('flightbooking.phone_number')}} *</label>
                        <input type="tel" class="{{ $lockedFieldClass }}" name="user[phone]" placeholder="" value="{{ old('user.phone', $loggedInCustomer->phone ?? '') }}" @if($loggedInCustomer) readonly @else required @endif>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{t('flightbooking.address')}} *</label>
                    <input type="text" class="{{ $lockedFieldClass }}" name="user[address]" placeholder="{{t('flightbooking.enter_your_address')}}" value="{{ old('user.address', $loggedInCustomer->address ?? '') }}" @if($loggedInCustomer) readonly @else required @endif>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{t('flightbooking.city')}} *</label>
                        <input type="text" class="{{ $lockedFieldClass }}" name="user[city]" placeholder="{{t('flightbooking.enter_city')}}" value="{{ old('user.city', $loggedInCustomer->city ?? '') }}" @if($loggedInCustomer) readonly @else required @endif>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{t('flightbooking.country')}} *</label>
                        @if($loggedInCustomer)
                            <input type="text" class="{{ $lockedFieldClass }}" value="{{ $matchedCountry->country ?? $loggedInCustomer->country ?? '—' }}" readonly>
                            <input type="hidden" name="user[country]" value="{{ old('user.country', $matchedCountry ? strtolower($matchedCountry->country_code) : '') }}">
                        @else
                            <select class="{{ $lockedFieldClass }}" name="user[country]" required>
                                <option value="">{{t('flightbooking.select_country')}}</option>
                                @foreach($countries as $country)
                                    <option value="{{ strtolower($country->country_code) }}" {{ old('nationality') == strtolower($country->country_code) ? 'selected' : '' }}>
                                        {{ $country->country }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                </div>
            </div>
            @endunless

            <!-- Travellers Information -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-users text-blue-600 mr-3" style="color: #0077BE;"></i>
                    {{t('flightbooking.travellers_information')}}
                </h2>

                @for($i=1; $i<=$session_data['adults']; $i++)
                <div class="mb-8 last:mb-0 p-6 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border-2 border-blue-100">
                    <!-- Traveller Header -->
                    <div class="flex items-center justify-between mb-6 pb-4 border-b-2 border-blue-200">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center"  style="background-color: #0077BE;">
                                <i class="fas fa-user text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-800">{{t('flightbooking.traveller')}} {{$i}}</h3>
                                <span class="inline-block px-3 py-1 bg-blue-600 text-white text-xs font-semibold rounded-full mt-1" style="background-color: #0077BE;">{{t('flightbooking.adult')}}</span>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="traveller_type_{{$i}}" value="adults"/>

                    <!-- Gender and DOB Row -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{t('flightbooking.gender')}} *</label>
                            <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition bg-white" name="gender_{{$i}}" required>
                                <option value="">{{t('flightbooking.select_gender')}}</option>
                                <option value="m">{{t('flightbooking.male')}}</option>
                                <option value="f">{{t('flightbooking.female')}}</option>
                                <option value="o">{{t('flightbooking.other')}}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{t('flightbooking.date_of_birth')}} *</label>
                            <div class="grid grid-cols-3 gap-2">
                                <select class="px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition bg-white text-sm" name="dob_day_{{$i}}" required>
                                    <option value="">{{t('flightbooking.day')}}</option>
                                    @for($d=1; $d<=31; $d++)
                                    <option value="{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}">{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}</option>
                                    @endfor
                                </select>

                                <select class="px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition bg-white text-sm" name="dob_month_{{$i}}" required>
                                    <option value="">{{t('flightbooking.month')}}</option>
                                    <option value="1">{{t('flightbooking.jan')}}</option>
                                    <option value="2">{{t('flightbooking.feb')}}</option>
                                    <option value="3">{{t('flightbooking.mar')}}</option>
                                    <option value="4">{{t('flightbooking.apr')}}</option>
                                    <option value="5">{{t('flightbooking.may')}}</option>
                                    <option value="6">{{t('flightbooking.jun')}}</option>
                                    <option value="7">{{t('flightbooking.jul')}}</option>
                                    <option value="8">{{t('flightbooking.aug')}}</option>
                                    <option value="9">{{t('flightbooking.sep')}}</option>
                                    <option value="10">{{t('flightbooking.oct')}}</option>
                                    <option value="11">{{t('flightbooking.nov')}}</option>
                                    <option value="12">{{t('flightbooking.dec')}}</option>
                                </select>

                                <select class="px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition bg-white text-sm" name="dob_year_{{$i}}" required>
                                    <option value="">{{t('flightbooking.year')}}</option>
                                    @for($y=2023; $y>=1920; $y--)
                                    <option value="{{$y}}" {{ $y == 1984 ? t('flightbooking.selected') : '' }}>{{$y}}</option>

                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Name Fields -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{t('flightbooking.first_name')}} *</label>
                            <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition bg-white" name="first_name_{{$i}}" placeholder="{{t('flightbooking.as_per_passport')}}" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{t('flightbooking.last_name')}} *</label>
                            <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition bg-white" name="last_name_{{$i}}" placeholder="{{t('flightbooking.as_per_passport')}}" required>
                        </div>
                    </div>

                    <!-- Passport Details Section -->
                    <div class="mt-6 p-4 bg-white rounded-lg border border-gray-200">
                        <h4 class="font-semibold text-gray-700 mb-4 flex items-center">
                            <i class="fas fa-passport mr-2 text-blue-600" style="color: #0077BE;"></i>
                            {{t('flightbooking.passport_details')}}
                        </h4>

                        <!-- Passport Number and Nationality -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{t('flightbooking.passport_number')}} *</label>
                                <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" name="passport_{{$i}}" placeholder="AB123456" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{t('flightbooking.nationality')}} *</label>
                                <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" name="nationality_{{$i}}" required>
                                    <option value="">{{t('flightbooking.select_country')}}</option>
                                    @foreach($countries as $country)
                                        <option value="{{ strtolower($country->country_code) }}" {{ old('nationality') == strtolower($country->country_code) ? 'selected' : '' }}>
                                            {{ $country->country }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Passport Issuance Date -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{t('flightbooking.passport_issuance_date')}} *</label>
                            <div class="grid grid-cols-3 gap-2">
                                <select class="px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm" name="passport_issuance_day_{{$i}}" required>
                                    <option value="">{{t('flightbooking.day')}}</option>
                                    @for($d=1; $d<=31; $d++)
                                    <option value="{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}">{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}</option>
                                    @endfor
                                </select>

                                <select class="px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm" name="passport_issuance_month_{{$i}}" required>
                                    <option value="">{{t('flightbooking.month')}}</option>
                                    <option value="1">{{t('flightbooking.jan')}}</option>
                                    <option value="2">{{t('flightbooking.feb')}}</option>
                                    <option value="3">{{t('flightbooking.mar')}}</option>
                                    <option value="4">{{t('flightbooking.apr')}}</option>
                                    <option value="5">{{t('flightbooking.may')}}</option>
                                    <option value="6">{{t('flightbooking.jun')}}</option>
                                    <option value="7">{{t('flightbooking.jul')}}</option>
                                    <option value="8">{{t('flightbooking.aug')}}</option>
                                    <option value="9">{{t('flightbooking.sep')}}</option>
                                    <option value="10">{{t('flightbooking.oct')}}</option>
                                    <option value="11">{{t('flightbooking.nov')}}</option>
                                    <option value="12">{{t('flightbooking.dec')}}</option>
                                </select>

                                <select class="px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm" name="passport_issuance_year_{{$i}}" required>
                                    <option value="">{{t('flightbooking.year')}}</option>
                                    @for($y=2026; $y>=1920; $y--)
                                    <option value="{{$y}}" {{ $y == 2020 ? t('flightbooking.selected') : '' }}>{{$y}}</option>

                                    @endfor
                                </select>
                            </div>
                        </div>

                        <!-- Passport Expiry Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{t('flightbooking.passport_expiry_date')}} *</label>
                            <div class="grid grid-cols-3 gap-2">
                                <select class="px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm" name="passport_day_expiry_{{$i}}" required>
                                    <option value="">{{t('flightbooking.day')}}</option>
                                    @for($d=1; $d<=31; $d++)
                                    <option value="{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}">{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}</option>
                                    @endfor
                                </select>

                                <select class="px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm" name="passport_month_expiry_{{$i}}" required>
                                    <option value="">{{t('flightbooking.month')}}</option>
                                    <option value="1">{{t('flightbooking.jan')}}</option>
                                    <option value="2">{{t('flightbooking.feb')}}</option>
                                    <option value="3">{{t('flightbooking.mar')}}</option>
                                    <option value="4">{{t('flightbooking.apr')}}</option>
                                    <option value="5">{{t('flightbooking.may')}}</option>
                                    <option value="6">{{t('flightbooking.jun')}}</option>
                                    <option value="7">{{t('flightbooking.jul')}}</option>
                                    <option value="8">{{t('flightbooking.aug')}}</option>
                                    <option value="9">{{t('flightbooking.sep')}}</option>
                                    <option value="10">{{t('flightbooking.oct')}}</option>
                                    <option value="11">{{t('flightbooking.nov')}}</option>
                                    <option value="12">{{t('flightbooking.dec')}}</option>
                                </select>

                                <select class="px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm" name="passport_year_expiry_{{$i}}" required>
                                    <option value="">{{t('flightbooking.year')}}</option>
                                    @for($y=2043; $y>=2023; $y--)
                                    <option value="{{$y}}" {{ $y == 2025 ? t('flightbooking.selected') : '' }}>{{$y}}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                @endfor


                    @for($i=1; $i<=$session_data['children']; $i++)
                        <div class="mb-8 last:mb-0 p-6 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border-2 border-blue-100">
                            <!-- Traveller Header -->
                            <div class="flex items-center justify-between mb-6 pb-4 border-b-2 border-blue-200">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center"  style="background-color: #0077BE;">
                                        <i class="fas fa-user text-white text-lg"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-800">{{t('flightbooking.traveller')}} {{$i}}</h3>
                                        <span class="inline-block px-3 py-1 bg-blue-600 text-white text-xs font-semibold rounded-full mt-1" style="background-color: #0077BE;">{{t('flightbooking.children')}}</span>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="children_traveller_type_{{$i}}" value="children"/>

                            <!-- Gender and DOB Row -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">{{t('flightbooking.gender')}} *</label>
                                    <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition bg-white" name="children_gender_{{$i}}" required>
                                        <option value="">{{t('flightbooking.select_gender')}}</option>
                                        <option value="m">{{t('flightbooking.male')}}</option>
                                        <option value="f">{{t('flightbooking.female')}}</option>
                                        <option value="o">{{t('flightbooking.other')}}</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">{{t('flightbooking.date_of_birth')}} *</label>
                                    <div class="grid grid-cols-3 gap-2">
                                        <select class="px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition bg-white text-sm" name="children_dob_day_{{$i}}" required>
                                            <option value="">{{t('flightbooking.day')}}</option>
                                            @for($d=1; $d<=31; $d++)
                                                <option value="{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}">{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}</option>
                                            @endfor
                                        </select>

                                        <select class="px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition bg-white text-sm" name="children_dob_month_{{$i}}" required>
                                            <option value="">{{t('flightbooking.month')}}</option>
                                            <option value="1">{{t('flightbooking.jan')}}</option>
                                            <option value="2">{{t('flightbooking.feb')}}</option>
                                            <option value="3">{{t('flightbooking.mar')}}</option>
                                            <option value="4">{{t('flightbooking.apr')}}</option>
                                            <option value="5">{{t('flightbooking.may')}}</option>
                                            <option value="6">{{t('flightbooking.jun')}}</option>
                                            <option value="7">{{t('flightbooking.jul')}}</option>
                                            <option value="8">{{t('flightbooking.aug')}}</option>
                                            <option value="9">{{t('flightbooking.sep')}}</option>
                                            <option value="10">{{t('flightbooking.oct')}}</option>
                                            <option value="11">{{t('flightbooking.nov')}}</option>
                                            <option value="12">{{t('flightbooking.dec')}}</option>
                                        </select>

                                        <select class="px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition bg-white text-sm" name="children_dob_year_{{$i}}" required>
                                            <option value="">{{t('flightbooking.year')}}</option>
                                            @for($y = date('Y'); $y >= date('Y') - 17; $y--)
                                                <option value="{{ $y }}">{{ $y }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Name Fields -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">{{t('flightbooking.first_name')}} *</label>
                                    <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition bg-white" name="children_first_name_{{$i}}" placeholder="{{t('flightbooking.as_per_passport')}}" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">{{t('flightbooking.last_name')}} *</label>
                                    <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition bg-white" name="children_last_name_{{$i}}" placeholder="{{t('flightbooking.as_per_passport')}}" required>
                                </div>
                            </div>



                            <div class="mt-6 p-4 bg-white rounded-lg border border-gray-200">
                                <h4 class="font-semibold text-gray-700 mb-4 flex items-center">
                                    <i class="fas fa-passport mr-2 text-blue-600" style="color: #0077BE;"></i>
                                    {{t('flightbooking.passport_details')}}
                                </h4>

                                <!-- Passport Number and Nationality -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">{{t('flightbooking.passport_number')}} *</label>
                                        <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" name="children_passport_{{$i}}" placeholder="AB123456" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">{{t('flightbooking.nationality')}} *</label>
                                        <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" name="nchildren_ationality_{{$i}}" required>
                                            <option value="">{{t('flightbooking.select_country')}}</option>
                                            @foreach($countries as $country)
                                                <option value="{{ strtolower($country->country_code) }}" {{ old('nationality') == strtolower($country->country_code) ? 'selected' : '' }}>
                                                    {{ $country->country }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Passport Issuance Date -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">{{t('flightbooking.passport_issuance_date')}} *</label>
                                    <div class="grid grid-cols-3 gap-2">
                                        <select class="px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm" name="children_passport_issuance_day_{{$i}}" required>
                                            <option value="">{{t('flightbooking.day')}}</option>
                                            @for($d=1; $d<=31; $d++)
                                                <option value="{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}">{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}</option>
                                            @endfor
                                        </select>

                                        <select class="px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm" name="children_passport_issuance_month_{{$i}}" required>
                                            <option value="">{{t('flightbooking.month')}}</option>
                                            <option value="1">{{t('flightbooking.jan')}}</option>
                                            <option value="2">{{t('flightbooking.feb')}}</option>
                                            <option value="3">{{t('flightbooking.mar')}}</option>
                                            <option value="4">{{t('flightbooking.apr')}}</option>
                                            <option value="5">{{t('flightbooking.may')}}</option>
                                            <option value="6">{{t('flightbooking.jun')}}</option>
                                            <option value="7">{{t('flightbooking.jul')}}</option>
                                            <option value="8">{{t('flightbooking.aug')}}</option>
                                            <option value="9">{{t('flightbooking.sep')}}</option>
                                            <option value="10">{{t('flightbooking.oct')}}</option>
                                            <option value="11">{{t('flightbooking.nov')}}</option>
                                            <option value="12">{{t('flightbooking.dec')}}</option>
                                        </select>

                                        <select class="px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm" name="children_passport_issuance_year_{{$i}}" required>
                                            <option value="">{{t('flightbooking.year')}}</option>
                                            @for($y=2026; $y>=1920; $y--)
                                                <option value="{{$y}}" {{ $y == 2020 ? t('flightbooking.selected') : '' }}>{{$y}}</option>

                                            @endfor
                                        </select>
                                    </div>
                                </div>

                                <!-- Passport Expiry Date -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">{{t('flightbooking.passport_expiry_date')}} *</label>
                                    <div class="grid grid-cols-3 gap-2">
                                        <select class="px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm" name="children_passport_day_expiry_{{$i}}" required>
                                            <option value="">{{t('flightbooking.day')}}</option>
                                            @for($d=1; $d<=31; $d++)
                                                <option value="{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}">{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}</option>
                                            @endfor
                                        </select>

                                        <select class="px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm" name="children_passport_month_expiry_{{$i}}" required>
                                            <option value="">{{t('flightbooking.month')}}</option>
                                            <option value="1">{{t('flightbooking.jan')}}</option>
                                            <option value="2">{{t('flightbooking.feb')}}</option>
                                            <option value="3">{{t('flightbooking.mar')}}</option>
                                            <option value="4">{{t('flightbooking.apr')}}</option>
                                            <option value="5">{{t('flightbooking.may')}}</option>
                                            <option value="6">{{t('flightbooking.jun')}}</option>
                                            <option value="7">{{t('flightbooking.jul')}}</option>
                                            <option value="8">{{t('flightbooking.aug')}}</option>
                                            <option value="9">{{t('flightbooking.sep')}}</option>
                                            <option value="10">{{t('flightbooking.oct')}}</option>
                                            <option value="11">{{t('flightbooking.nov')}}</option>
                                            <option value="12">{{t('flightbooking.dec')}}</option>
                                        </select>

                                        <select class="px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm" name="children_passport_year_expiry_{{$i}}" required>
                                            <option value="">{{t('flightbooking.year')}}</option>
                                            @for($y=2043; $y>=2023; $y--)
                                                <option value="{{$y}}" {{ $y == 2025 ? t('flightbooking.selected') : '' }}>{{$y}}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endfor


                @for($i=1; $i<=$session_data['infants']; $i++)
                    <div class="mb-8 last:mb-0 p-6 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border-2 border-blue-100">
                        <!-- Traveller Header -->
                        <div class="flex items-center justify-between mb-6 pb-4 border-b-2 border-blue-200">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center"  style="background-color: #0077BE;">
                                    <i class="fas fa-user text-white text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-800">{{t('flightbooking.traveller')}} {{$i}}</h3>
                                    <span class="inline-block px-3 py-1 bg-blue-600 text-white text-xs font-semibold rounded-full mt-1" style="background-color: #0077BE;">{{t('flightbooking.infant')}}</span>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="infant_traveller_type_{{$i}}" value="infant"/>

                        <!-- Gender and DOB Row -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{t('flightbooking.gender')}} *</label>
                                <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition bg-white" name="infant_gender_{{$i}}" required>
                                    <option value="">{{t('flightbooking.select_gender')}}</option>
                                    <option value="m">{{t('flightbooking.male')}}</option>
                                    <option value="f">{{t('flightbooking.female')}}</option>
                                    <option value="o">{{t('flightbooking.other')}}</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{t('flightbooking.date_of_birth')}} *</label>
                                <div class="grid grid-cols-3 gap-2">
                                    <select class="px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition bg-white text-sm" name="infant_dob_day_{{$i}}" required>
                                        <option value="">{{t('flightbooking.day')}}</option>
                                        @for($d=1; $d<=31; $d++)
                                            <option value="{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}">{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}</option>
                                        @endfor
                                    </select>

                                    <select class="px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition bg-white text-sm" name="infant_dob_month_{{$i}}" required>
                                        <option value="">{{t('flightbooking.month')}}</option>
                                        <option value="1">{{t('flightbooking.jan')}}</option>
                                        <option value="2">{{t('flightbooking.feb')}}</option>
                                        <option value="3">{{t('flightbooking.mar')}}</option>
                                        <option value="4">{{t('flightbooking.apr')}}</option>
                                        <option value="5">{{t('flightbooking.may')}}</option>
                                        <option value="6">{{t('flightbooking.jun')}}</option>
                                        <option value="7">{{t('flightbooking.jul')}}</option>
                                        <option value="8">{{t('flightbooking.aug')}}</option>
                                        <option value="9">{{t('flightbooking.sep')}}</option>
                                        <option value="10">{{t('flightbooking.oct')}}</option>
                                        <option value="11">{{t('flightbooking.nov')}}</option>
                                        <option value="12">{{t('flightbooking.dec')}}</option>
                                    </select>

                                    <select class="px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition bg-white text-sm" name="infant_dob_year_{{$i}}" required>
                                        <option value="">{{t('flightbooking.year')}}</option>
                                        @for($y = date('Y'); $y >= date('Y') - 2; $y--)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Name Fields -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{t('flightbooking.first_name')}} *</label>
                                <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition bg-white" name="infant_first_name_{{$i}}" placeholder="{{t('flightbooking.as_per_passport')}}" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{t('flightbooking.last_name')}} *</label>
                                <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition bg-white" name="infant_last_name_{{$i}}" placeholder="{{t('flightbooking.as_per_passport')}}" required>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>



            <!-- Payment Methods -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-credit-card text-blue-600 mr-3" style="color: #0077BE;"></i>
                {{t('flightbooking.payment_methods')}}
            </h2>

            @foreach($payment as $key=>$value)
            <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition" style="margin-top: 5px;">
                <input type="radio" name="accept_payment" value="{{$value->name}}" checked class="w-5 h-5 text-blue-600">
                <img src="{{ url('public/assets/images/settings/payment/'.$value->name.'.png') }}" alt="Stripe" class="w-12 h-12 rounded-full object-cover ml-4">
                <div class="ml-4">
                    <div class="font-semibold text-gray-800">{{ucfirst($value->name)}}</div>
                </div>
            </label>
            @endforeach

            @if($agentWallet)
            <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-green-500 hover:bg-green-50 transition" style="margin-top: 5px;">
                <input type="radio" name="accept_payment" value="agent_wallet" class="w-5 h-5 text-green-600">
                <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center ml-4 flex-shrink-0">
                    <i class="fas fa-wallet text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <div class="font-semibold text-gray-800">Agent Wallet</div>
                    <div class="text-sm text-gray-500">Available Balance: <span class="font-semibold text-green-600">PKR {{ number_format($agentWallet->balance, 2) }}</span></div>
                </div>
            </label>
            @endif

        </div>


            <!-- Action Buttons -->
            <div class="flex gap-4">
                <button type="button" class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition flex items-center justify-center gap-2">
                    <i class="fas fa-arrow-left"></i>
                    {{t('flightbooking.back')}}
                </button>
                <button type="submit" style="background-color: #0077BE;" class="flex-1 px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition flex items-center justify-center gap-2">
                    {{t('flightbooking.pay_now')}}
                    <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>

        <!-- Right Sidebar (Flight Details) - Keep your existing sidebar code here -->
        <div class="lg:w-96">
    <div class="right-sidebar">
        <!-- Flight Details - One Way -->
        @if(isset($session_data['trip_type']) && $session_data['trip_type'] == "oneway")
            @foreach($routes->segments as $rout)
        <div class="sidebar-card">
            <div class="sidebar-card-header">
                <div class="sidebar-card-title">{{t('flightbooking.outbound_flight')}}</div>
            </div>
            <div class="sidebar-card-body">
                <!-- Timer -->
                <div class="timer-box">
                    <div class="timer-icon">
                        <i class="fas fa-hourglass-end"></i>
                    </div>
                    <div class="timer-content">
                        <div class="timer-label">{{t('flightbooking.complete_booking_in')}}</div>
                        <div class="timer-time" id="timer">30:00</div>
                    </div>
                </div>

                <!-- Airline Info -->
                <div class="flight-airline">
                    <div class="airline-logo">KU</div>
                    <div class="airline-info">
                        <div class="airline-name">{{$rout[0]->airline_name}}</div>
                        <div class="airline-flight">Flight {{$rout[0]->flight_number}} • {{ $rout[0]->departure->airport }}- {{ end($rout)->arrival->airport }}</div>
                    </div>
                </div>

                <div class="flight-detail">
                    <div class="flight-detail-label">{{t('flightbooking.route')}}</div>
                    <div class="flight-route">
                        <span class="flight-route-item">  {{ $rout[0]->departure->airport }}</span>
                        <span class="flight-route-arrow">→</span>
                        <span class="flight-route-item">{{ end($rout)->arrival->airport }}</span>
                    </div>
                </div>

                <div class="flight-detail">
                    <div class="flight-detail-label">{{t('flightbooking.date')}}</div>
                    <div class="flight-detail-value">{{ $rout[0]->departure->date_convert }}</div>
                </div>

                <div class="flight-detail">
                    <div class="flight-detail-label">{{t('flightbooking.time')}}</div>
                    <div class="flight-detail-value">{{ $rout[0]->departure->time }} - {{ $rout[0]->arrival->time }}</div>
                </div>
                @php
                    $oneWaySegments = $rout;
                    $stopsCount = count($oneWaySegments) - 1;

                    if ($stopsCount == 0) {
                        $stop = "";
                    } else {
                        $stop = "($stopsCount Stop)";
                    }
                @endphp

               <div class="flight-detail">
                   <div class="flight-detail-label">{{t('flightbooking.duration')}}</div>
                   <div class="flight-detail-value">{{$rout[0]->total_duration}} {{$stop}}</div>
               </div>

               <div class="flight-detail">
                   <div class="flight-detail-label">{{t('flightbooking.baggage')}}</div>
                   <div class="flight-detail-value">{{ $rout[0]->baggage}} + {{ $rout[0]->cabin_baggage}}</div>
               </div>
           </div>
       </div>
            @endforeach
       @endif

       @if(isset($session_data['trip_type']) && $session_data['trip_type'] == 'round')
           @php
               $rout = $routes->segments;
               @endphp

                <div class="sidebar-card">
                    <div class="sidebar-card-header">
                        <div class="sidebar-card-title">{{t('flightbooking.outbound_flight')}}</div>
                    </div>
                    <div class="sidebar-card-body">
                        <!-- Timer -->
                        <div class="timer-box">
                            <div class="timer-icon">
                                <i class="fas fa-hourglass-end"></i>
                            </div>
                            <div class="timer-content">
                                <div class="timer-label">{{t('flightbooking.complete_booking_in')}}</div>
                                <div class="timer-time" id="timer">30:00</div>
                            </div>
                        </div>

                        <!-- Airline Info -->
                        <div class="flight-airline">
                            <div class="airline-logo">{{$rout[0][0]->carrier->marketing}}</div>
                            <div class="airline-info">
                                <div class="airline-name">{{$rout[0][0]->airline_name}}</div>
                                <div class="airline-flight">Flight {{$rout[0][0]->flight_number}} • {{ $rout[0][0]->departure->airport }}- {{ end($rout[0])->arrival->airport }}</div>
                            </div>
                        </div>

                        <div class="flight-detail">
                            <div class="flight-detail-label">{{t('flightbooking.route')}}</div>
                            <div class="flight-route">
                                <span class="flight-route-item">  {{ $rout[0][0]->departure->airport }}</span>
                                <span class="flight-route-arrow">→</span>
                                <span class="flight-route-item">{{ end($rout[0])->arrival->airport }}</span>
                            </div>
                        </div>

                        <div class="flight-detail">
                            <div class="flight-detail-label">{{t('flightbooking.date')}}</div>
                            <div class="flight-detail-value">{{ $rout[0][0]->departure->date_convert }}</div>
                        </div>

                        <div class="flight-detail">
                            <div class="flight-detail-label">{{t('flightbooking.time')}}</div>
                            <div class="flight-detail-value">{{ $rout[0][0]->departure->time }} - {{ $rout[0][0]->arrival->time }}</div>
                        </div>
                        @php
                            $oneWaySegments = $rout[0];
                            $stopsCount = count($oneWaySegments) - 1;

                            if ($stopsCount == 0) {
                                $stop = "";
                            } else {
                                $stop = "($stopsCount Stop)";
                            }
                        @endphp

                        <div class="flight-detail">
                            <div class="flight-detail-label">{{t('flightbooking.duration')}}</div>
                            <div class="flight-detail-value">{{$rout[0][0]->total_duration}} {{$stop}}</div>
                        </div>

                        <div class="flight-detail">
                            <div class="flight-detail-label">{{t('flightbooking.baggage')}}</div>
                            <div class="flight-detail-value">{{ $rout[0][0]->baggage}} + {{ $rout[0][0]->cabin_baggage}}</div>
                        </div>
                    </div>
                </div>

       <!-- Flight Details - Return -->
       <div class="sidebar-card">
           <div class="sidebar-card-header">
               <div class="sidebar-card-title">{{t('flightbooking.return_flight')}}</div>
           </div>
           <div class="sidebar-card-body">
               <!-- Airline Info -->
               <div class="flight-airline">
                   <div class="airline-logo">{{$rout[1][0]->carrier->marketing}}</div>
                   <div class="airline-info">
                       <div class="airline-name">{{$rout[1][0]->airline_name}}</div>
                       <div class="airline-flight">Flight {{$rout[1][0]->flight_number}} • {{ $rout[1][0]->departure->airport }}- {{ end($rout[1])->arrival->airport }}</div>
                   </div>
               </div>

               <div class="flight-detail">
                   <div class="flight-detail-label">{{t('flightbooking.route')}}</div>
                   <div class="flight-route">
                       <div class="flight-route">
                           <span class="flight-route-item">  {{ $rout[1][0]->departure->airport }}</span>
                           <span class="flight-route-arrow">→</span>
                           <span class="flight-route-item">{{ end($rout[1])->arrival->airport }}</span>
                       </div>
                   </div>
               </div>

               <div class="flight-detail">
                   <div class="flight-detail-label">{{t('flightbooking.date')}}</div>
                   <div class="flight-detail-value">{{ $rout[1][0]->departure->date_convert }}</div>
               </div>

               <div class="flight-detail">
                   <div class="flight-detail-label">{{t('flightbooking.time')}}</div>
                   <div class="flight-detail-value">{{ $rout[1][0]->departure->time }} - {{ $rout[1][0]->arrival->time }}</div>
               </div>

               @php
                   $retunrSegments = $rout[1];
                   $retunrCount = count($retunrSegments) - 1;

                   if ($retunrCount == 0) {
                       $return_stop = "";
                   } else {
                       $return_stop = "($retunrCount Stop)";
                   }
               @endphp
               <div class="flight-detail">
                   <div class="flight-detail-label">{{t('flightbooking.duration')}}</div>
                   <div class="flight-detail-value">{{$rout[1][0]->total_duration}} {{$return_stop}}</div>
               </div>

               <div class="flight-detail">
                   <div class="flight-detail-label">{{t('flightbooking.baggage')}}</div>
                   <div class="flight-detail-value">{{ $rout[0][0]->baggage}} + {{ $rout[0][0]->cabin_baggage}}</div>
               </div>
           </div>
       </div>
       @endif

       <!-- Price Summary -->
       @php
           // Shown converted into whichever currency is active site-wide
           // (System Settings > Currencies / admin/currencies), using the
           // admin's own configured exchange rates — not just relabeled, so
           // the number on screen is correct even if the supplier priced the
           // search in a different currency.
           $__activeCurrency = activeCurrency();
           $__activeCurrencyCode = $__activeCurrency->currency_name ?? $routes->segments[0][0]->currency;
           $__displayedFare = $__activeCurrency
               ? convertCurrency($routes->segments[0][0]->price, $routes->segments[0][0]->currency, $__activeCurrencyCode)
               : $routes->segments[0][0]->price;
       @endphp
       <div class="sidebar-card">
           <div class="sidebar-card-header">
               <div class="sidebar-card-title">{{t('flightbooking.price_summary')}}</div>
           </div>
           <div class="sidebar-card-body">
               <div class="price-summary">
                   <div class="price-row">
                       <span class="price-label">{{t('flightbooking.base_fare')}}</span>
                       <span class="price-value">{{$__activeCurrencyCode}} {{number_format($__displayedFare, 2)}}</span>
                   </div>
                   <div class="price-row">
                       <span class="price-label">{{t('flightbooking.taxes_fees')}}</span>
                       <span class="price-value">{{$__activeCurrencyCode}} 0.00</span>
                   </div>
                   <div class="price-row">
                       <span class="price-label price-total">{{t('flightbooking.total')}}</span>
                       <span class="price-value price-total">{{$__activeCurrencyCode}} {{number_format($__displayedFare, 2)}}</span>
                   </div>
               </div>
           </div>
       </div>
   </div>
        </div>
    </form>
</div>

<script>
   // 30 Minute Timer
   function startTimer() {
       let timeLeft = 30 * 60; // 30 minutes in seconds
       const timerDisplay = document.getElementById('timer');

       const interval = setInterval(() => {
           let minutes = Math.floor(timeLeft / 60);
           let seconds = timeLeft % 60;

           timerDisplay.textContent =
               String(minutes).padStart(2, '0') + ':' +
               String(seconds).padStart(2, '0');

           // Change color when time is running out
           if (timeLeft < 300) { // Last 5 minutes
               timerDisplay.style.color = '#DC2626';
           }

           if (timeLeft === 0) {
               clearInterval(interval);
               timerDisplay.textContent = '00:00';
               alert('Booking time expired! Please start over.');
           }

           timeLeft--;
       }, 1000);
   }

   // Start timer when page loads
   document.addEventListener('DOMContentLoaded', startTimer);
</script>


@endsection
