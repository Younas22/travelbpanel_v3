@extends('common.layout')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    @if(session('error'))
    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg flex items-center gap-3">
        <i class="fas fa-exclamation-circle text-red-500"></i>
        {{ session('error') }}
    </div>
    @endif
    <!-- Main Content -->
    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Booking Form -->
        <div class="flex-1">
            <form action="{{ route('booking') }}" method="POST">
                @csrf
                <input type="hidden" name="room_data" value="{{ encrypt(json_encode($room_data)) }}">
                <input type="hidden" name="room" value="{{ encrypt(json_encode($room)) }}">
                <input type="hidden" name="option" value="{{ encrypt(json_encode($booking_option)) }}">
                <input type="hidden" name="booking_data" value="{{ encrypt(json_encode($booking_data)) }}">

                <!-- Guest Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-5 flex items-center">
                        <i class="fas fa-user mr-3" style="color: #0077BE;"></i>
                        {{t('hotelbooking.guest_information')}}
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{t('hotelbooking.first_name')}} *</label>
                            <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition" style="focus:ring-color: #0077BE;" name="user[first_name]" placeholder="{{t('hotelbooking.enter_first_name')}}" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{t('hotelbooking.last_name')}} *</label>
                            <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition" name="user[last_name]" placeholder="{{t('hotelbooking.enter_last_name')}}" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{t('hotelbooking.email_address')}} *</label>
                            <input type="email" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition" name="user[email]" placeholder="example@email.com" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{t('hotelbooking.phone_number')}} *</label>
                            <input type="tel" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition" name="user[phone]" placeholder="+92 300 1234567" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{t('hotelbooking.country')}} *</label>
                            <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition bg-white" name="user[country]" required>
                                <option value="">{{t('flightbooking.select_country')}}</option>
                                @foreach($countries as $country)
                                    <option value="{{ strtolower($country->country_code) }}" {{ old('nationality') == strtolower($country->country_code) ? 'selected' : '' }}>
                                        {{ $country->country }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Travellers Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-5 flex items-center">
                        <i class="fas fa-users mr-3" style="color: #0077BE;"></i>
                        {{t('hotelbooking.travellers_information')}}
                    </h2>

                    @for($i=1; $i<=$hotel_search['adults']; $i++)
                    <!-- Adult Traveller -->
                    <div class="mb-6 last:mb-0 p-5 rounded-xl border-2" style="background: linear-gradient(to right, #E6F3FA, #F0F8FC); border-color: #0077BE;">
                        <div class="flex items-center justify-between mb-4 pb-3 border-b-2" style="border-color: #0077BE;">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background-color: #0077BE;">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">{{t('hotelbooking.traveller')}} {{$i}}</h3>
                                    <span class="inline-block px-3 py-0.5 text-white text-xs font-semibold rounded-full mt-0.5" style="background-color: #0077BE;">{{t('hotelbooking.adult')}}</span>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="traveller_type_{{$i}}" value="adults"/>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{t('hotelbooking.gender')}} *</label>
                                <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition bg-white" name="adult_gender_{{$i}}" required>
                                    <option value="">{{t('hotelbooking.select_gender')}}</option>
                                    <option value="male">{{t('hotelbooking.male')}}</option>
                                    <option value="female">{{t('hotelbooking.female')}}</option>
                                    <option value="other">{{t('hotelbooking.other')}}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{t('hotelbooking.first_name')}} *</label>
                                <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition bg-white" name="adult_first_name_{{$i}}" placeholder="{{t('hotelbooking.enter_first_name')}}" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{t('hotelbooking.last_name')}} *</label>
                                <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition bg-white" name="adult_last_name_{{$i}}" placeholder="{{t('hotelbooking.enter_last_name')}}" required>
                            </div>
                        </div>
                    </div>
                    @endfor

                    @for($i=1; $i<=$hotel_search['childs']; $i++)
                    <!-- Child Traveller -->
                    <div class="mb-6 last:mb-0 p-5 rounded-xl border-2 mt-6" style="background: linear-gradient(to right, #E6F3FA, #F0F8FC); border-color: #0077BE;">
                        <div class="flex items-center justify-between mb-4 pb-3 border-b-2" style="border-color: #0077BE;">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background-color: #0077BE;">
                                    <i class="fas fa-child text-white"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">{{t('hotelbooking.child')}} {{$i}}</h3>
                                    <span class="inline-block px-3 py-0.5 text-white text-xs font-semibold rounded-full mt-0.5" style="background-color: #0077BE;">{{t('hotelbooking.children')}}</span>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="traveller_child_{{$i}}" value="child"/>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{t('hotelbooking.gender')}} *</label>
                                <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition bg-white" name="child_gender_{{$i}}" required>
                                    <option value="">{{t('hotelbooking.select_gender')}}</option>
                                    <option value="male">{{t('hotelbooking.male')}}</option>
                                    <option value="female">{{t('hotelbooking.female')}}</option>
                                    <option value="other">{{t('hotelbooking.other')}}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{t('hotelbooking.first_name')}} *</label>
                                <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition bg-white" name="child_first_name_{{$i}}" placeholder="{{t('hotelbooking.enter_first_name')}}" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{t('hotelbooking.last_name')}} *</label>
                                <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition bg-white" name="child_last_name_{{$i}}" placeholder="{{t('hotelbooking.enter_last_name')}}" required>
                            </div>
                        </div>
                    </div>
                    @endfor
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-credit-card text-blue-600 mr-3" style="color: #0077BE;"></i>
                        {{t('flightbooking.payment_methods')}}
                    </h2>

                    @foreach($payment as $key=>$value)
                        <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition" style="margin-top: 5px;">
                            <input type="radio" name="accept_payment" value="{{$value->name}}" checked class="w-5 h-5 text-blue-600">
                            <img src="{{ url('public/assets/images/settings/payment/'.$value->name.'.png') }}" alt="{{$value->name}}" class="w-12 h-12 rounded-full object-cover ml-4">
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
                        <i class="fas fa-chevron-left"></i>
                        {{t('hotelbooking.back')}}
                    </button>
                    <button type="submit" class="flex-1 px-6 py-3 text-white font-semibold rounded-lg transition shadow-lg flex items-center justify-center gap-2" style="background-color: #0077BE;" onmouseover="this.style.backgroundColor='#005A9C'" onmouseout="this.style.backgroundColor='#0077BE'">
                        <i class="fas fa-check"></i>
                        {{t('hotelbooking.confirm_booking')}}
                    </button>
                </div>
            </form>
        </div>

        <!-- Booking Summary Sidebar -->
        <div class="lg:w-96">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 sticky top-4">
                <div class="text-white p-5 rounded-t-xl" style="background-color: #0077BE;">
                    <h3 class="text-lg font-bold">{{t('hotelbooking.booking_summary')}}</h3>
                </div>

                <div class="p-5">
                    <div class="mb-5 pb-5 border-b border-gray-200">
                        <h4 class="text-lg font-bold text-gray-800 mb-2">{{$booking_data['hotel_name']}}</h4>
                        <p class="text-sm text-gray-600 flex items-center gap-2 mb-2">
                            <i class="fas fa-map-pin" style="color: #0077BE;"></i>
                            {{$booking_data['address']}}
                        </p>
                        <div class="flex items-center gap-2">
                            <span class="text-yellow-500">{!! str_repeat('★', $booking_data['stars']) !!}</span>
                            <span class="text-sm text-gray-600">{{$booking_data['stars']}} {{t('hotelbooking.star_hotel')}}</span>
                        </div>
                    </div>

                    <div class="space-y-3 mb-5 pb-5 border-b border-gray-200">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{t('hotelbooking.room_type')}}</span>
                            <span class="font-semibold text-gray-800">{{$room->name}}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{t('hotelbooking.check_in')}}</span>
                            <span class="font-semibold text-gray-800">{{\Carbon\Carbon::parse($booking_data['checkin'])->format('M d, Y')}}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{t('hotelbooking.check_out')}}</span>
                            <span class="font-semibold text-gray-800">{{\Carbon\Carbon::parse($booking_data['checkout'])->format('M d, Y')}}</span>
                        </div>
                        @php
                            $days = (new DateTime($booking_data['checkin']))->diff(new DateTime($booking_data['checkout']))->days;
                        @endphp
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{t('hotelbooking.nights')}}</span>
                            <span class="font-semibold text-gray-800">{{$days}}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{t('hotelbooking.guests')}}</span>
                            <span class="font-semibold text-gray-800">{{$booking_data['adults']}} {{t('hotelbooking.adults')}}</span>
                        </div>
                    </div>

                    <div class="space-y-2 mb-5 pb-5 border-b border-gray-200">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{str_replace(':nights', $days, t('hotelbooking.room_charges'))}}</span>
                            <span class="font-semibold text-gray-800">{{$booking_option['price']}}</span>
                        </div>
                    </div>

                    @php
                        // Prefer the fresh, GetRooms-With-Blocking-derived taxes/fees
                        // (re-validated for THIS booking attempt); fall back to the
                        // figures captured at search time if that re-check didn't run.
                        $taxesIncluded   = $fresh_taxes_and_fees['included_in_price']   ?? ($booking_option['taxes_included']    ?? []);
                        $taxesAtProperty = $fresh_taxes_and_fees['payable_at_property'] ?? ($booking_option['taxes_at_property'] ?? []);
                    @endphp

                    @if(!empty($booking_option['non_refundable']) || !empty($booking_option['cancel_restricted']) || (!empty($booking_option['min_stay']) && (int) $booking_option['min_stay'] > 0))
                        <div class="space-y-2 mb-5 pb-5 border-b border-gray-200">
                            @if(!empty($booking_option['cancel_restricted']))
                                <div class="flex items-start gap-2 text-xs font-semibold text-red-700 bg-red-50 rounded-lg px-3 py-2">
                                    <i class="fas fa-ban mt-0.5"></i>
                                    <span>{{ $booking_option['cancel_restricted_note'] ?? 'Cancellation not allowed' }}</span>
                                </div>
                            @elseif(!empty($booking_option['non_refundable']))
                                <div class="flex items-start gap-2 text-xs font-semibold text-red-700 bg-red-50 rounded-lg px-3 py-2">
                                    <i class="fas fa-times-circle mt-0.5"></i>
                                    <span>{{ t('hoteldetails.non_refundable') }}</span>
                                </div>
                            @endif
                            @if(!empty($booking_option['min_stay']) && (int) $booking_option['min_stay'] > 0)
                                <div class="flex items-start gap-2 text-xs text-amber-800 bg-amber-50 rounded-lg px-3 py-2">
                                    <i class="fas fa-calendar-day mt-0.5"></i>
                                    <span>{{ str_replace(':nights', $booking_option['min_stay'], t('hoteldetails.min_stay_note')) }}</span>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if(!empty($taxesIncluded) || !empty($taxesAtProperty))
                        <div class="space-y-1 mb-5 pb-5 border-b border-gray-200">
                            <div class="text-sm font-semibold text-gray-700 mb-1">{{ t('hoteldetails.taxes_and_fees') }}</div>
                            @foreach($taxesIncluded as $tax)
                                <div class="flex justify-between text-xs text-gray-600">
                                    <span>{{ $tax['description'] ?: ($tax['name'] ?? $tax['type'] ?? '') }} <span class="text-green-700">({{ t('hoteldetails.included_in_price') }})</span></span>
                                    <span>{{ number_format($tax['amount'], 2) }} {{ $tax['currency'] ?? '' }}</span>
                                </div>
                            @endforeach
                            @foreach($taxesAtProperty as $tax)
                                <div class="flex justify-between text-xs text-gray-600">
                                    <span>{{ $tax['description'] ?: ($tax['name'] ?? $tax['type'] ?? '') }} <span class="text-amber-700">({{ t('hoteldetails.payable_at_property') }})</span></span>
                                    <span>{{ number_format($tax['amount'], 2) }} {{ $tax['currency'] ?? '' }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="flex justify-between items-center">
                        <span class="text-lg font-bold text-gray-800">{{t('hotelbooking.total_amount')}}</span>
                        <span class="text-2xl font-bold" style="color: #0077BE;">{{$booking_option['price']}}</span>
                    </div>

                    <div class="mt-4 text-center">
                        <p class="text-xs text-gray-500 flex items-center justify-center gap-1">
                            <i class="fas fa-lock"></i>
                            {{t('hotelbooking.secure_payment')}}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    input:focus, select:focus {
        ring-color: #0077BE !important;
        border-color: #0077BE !important;
    }
</style>
@endsection
