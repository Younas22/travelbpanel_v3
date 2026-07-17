@extends('common.layout')
@section('content')
    @php
        $active_currency = activeCurrency();
    @endphp
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
            <form action="{{ route('tour.booking.store') }}" method="POST">
                @csrf
                <input type="hidden" name="tour_data" value="{{ encrypt(json_encode($tour_data)) }}">
                <input type="hidden" name="search_params" value="{{ encrypt(json_encode($searchParams)) }}">

                <!-- Guest Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-5 flex items-center">
                        <i class="fas fa-user mr-3" style="color: #0077BE;"></i>
                        {{t('tourbooking.guest_information')}}
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{t('tourbooking.first_name')}} *</label>
                            <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition" style="focus:ring-color: #0077BE;" name="user[first_name]" placeholder="{{t('tourbooking.enter_first_name')}}" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{t('tourbooking.last_name')}} *</label>
                            <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition" name="user[last_name]" placeholder="{{t('tourbooking.enter_last_name')}}" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{t('tourbooking.email_address')}} *</label>
                            <input type="email" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition" name="user[email]" placeholder="example@email.com" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{t('tourbooking.phone_number')}} *</label>
                            <input type="tel" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition" name="user[phone]" placeholder="+92 300 1234567" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{t('tourbooking.country')}} *</label>
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
                        {{t('tourbooking.travellers_information')}}
                    </h2>

                    @for($i=1; $i<=$searchParams['adult']; $i++)
                    <!-- Adult Traveller -->
                    <div class="mb-6 last:mb-0 p-5 rounded-xl border-2" style="background: linear-gradient(to right, #E6F3FA, #F0F8FC); border-color: #0077BE;">
                        <div class="flex items-center justify-between mb-4 pb-3 border-b-2" style="border-color: #0077BE;">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background-color: #0077BE;">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">{{t('tourbooking.traveller')}} {{$i}}</h3>
                                    <span class="inline-block px-3 py-0.5 text-white text-xs font-semibold rounded-full mt-0.5" style="background-color: #0077BE;">{{t('tourbooking.adult')}}</span>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="traveller_type_{{$i}}" value="adults"/>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{t('tourbooking.gender')}} *</label>
                                <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition bg-white" name="adult_gender_{{$i}}" required>
                                    <option value="">{{t('tourbooking.select_gender')}}</option>
                                    <option value="male">{{t('tourbooking.male')}}</option>
                                    <option value="female">{{t('tourbooking.female')}}</option>
                                    <option value="other">{{t('tourbooking.other')}}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{t('tourbooking.first_name')}} *</label>
                                <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition bg-white" name="adult_first_name_{{$i}}" placeholder="{{t('tourbooking.enter_first_name')}}" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{t('tourbooking.last_name')}} *</label>
                                <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition bg-white" name="adult_last_name_{{$i}}" placeholder="{{t('tourbooking.enter_last_name')}}" required>
                            </div>
                        </div>
                    </div>
                    @endfor

                    @for($i=1; $i<=$searchParams['child']; $i++)
                    <!-- Child Traveller -->
                    <div class="mb-6 last:mb-0 p-5 rounded-xl border-2 mt-6" style="background: linear-gradient(to right, #E6F3FA, #F0F8FC); border-color: #0077BE;">
                        <div class="flex items-center justify-between mb-4 pb-3 border-b-2" style="border-color: #0077BE;">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background-color: #0077BE;">
                                    <i class="fas fa-child text-white"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">{{t('tourbooking.child')}} {{$i}}</h3>
                                    <span class="inline-block px-3 py-0.5 text-white text-xs font-semibold rounded-full mt-0.5" style="background-color: #0077BE;">{{t('tourbooking.children')}}</span>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="traveller_child_{{$i}}" value="child"/>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{t('tourbooking.gender')}} *</label>
                                <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition bg-white" name="child_gender_{{$i}}" required>
                                    <option value="">{{t('tourbooking.select_gender')}}</option>
                                    <option value="male">{{t('tourbooking.male')}}</option>
                                    <option value="female">{{t('tourbooking.female')}}</option>
                                    <option value="other">{{t('tourbooking.other')}}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{t('tourbooking.first_name')}} *</label>
                                <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition bg-white" name="child_first_name_{{$i}}" placeholder="{{t('tourbooking.enter_first_name')}}" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{t('tourbooking.last_name')}} *</label>
                                <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition bg-white" name="child_last_name_{{$i}}" placeholder="{{t('tourbooking.enter_last_name')}}" required>
                            </div>
                        </div>
                    </div>
                    @endfor
                </div>

                <!-- Payment Methods -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-5 flex items-center">
                        <i class="fas fa-credit-card mr-3" style="color: #0077BE;"></i>
                        {{t('flightbooking.payment_methods')}}
                    </h2>

                    @foreach($payment as $key=>$value)
                    <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition" style="margin-top: 5px;">
                        <input type="radio" name="accept_payment" value="{{$value->name}}" {{ $loop->first ? 'checked' : '' }} class="w-5 h-5 text-blue-600">
                        <img src="{{ url('public/assets/images/settings/payment/'.$value->name.'.png') }}" alt="{{ucfirst($value->name)}}" class="w-12 h-12 rounded-full object-cover ml-4">
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
                    <a href="{{ url()->previous() }}" class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition flex items-center justify-center gap-2">
                        <i class="fas fa-chevron-left"></i>
                        {{t('tourbooking.back')}}
                    </a>
                    <button type="submit" class="flex-1 px-6 py-3 text-white font-semibold rounded-lg transition shadow-lg flex items-center justify-center gap-2" style="background-color: #0077BE;" onmouseover="this.style.backgroundColor='#005A9C'" onmouseout="this.style.backgroundColor='#0077BE'">
                        <i class="fas fa-check"></i>
                        {{t('tourbooking.confirm_booking')}}
                    </button>
                </div>
            </form>
        </div>

        <!-- Booking Summary Sidebar -->
        <div class="lg:w-96">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 sticky top-4">
                <div class="text-white p-5 rounded-t-xl" style="background-color: #0077BE;">
                    <h3 class="text-lg font-bold">{{t('tourbooking.booking_summary')}}</h3>
                </div>

                <div class="p-5">
                    <div class="mb-5 pb-5 border-b border-gray-200">
                        <h4 class="text-lg font-bold text-gray-800 mb-2">{{ $tour->name }}</h4>
                        <p class="text-sm text-gray-600 flex items-center gap-2 mb-2">
                            <i class="fas fa-map-pin" style="color: #0077BE;"></i>
                            {{ $tour->location_name ?? $tour->loaction }}
                        </p>
                        @if($tour->stars)
                        <div class="flex items-center gap-2">
                            <span class="text-yellow-500">{!! str_repeat('★', $tour->stars) !!}</span>
                            <span class="text-sm text-gray-600">{{ $tour->stars }} {{t('tourbooking.star_tour')}}</span>
                        </div>
                        @endif
                    </div>

                    <div class="space-y-3 mb-5 pb-5 border-b border-gray-200">
                        @if($tour->packageType)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{t('tourbooking.tour_type')}}</span>
                            <span class="font-semibold text-gray-800">{{ $tour->packageType->packege_type }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{t('tourbooking.duration')}}</span>
                            <span class="font-semibold text-gray-800">{{ $tour->duration }}</span>
                        </div>
                        @if($tour->days)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{t('tourbooking.days')}}</span>
                            <span class="font-semibold text-gray-800">{{ $tour->days }} {{t('tourbooking.days')}}</span>
                        </div>
                        @endif
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{t('tourbooking.departure_date')}}</span>
                            <span class="font-semibold text-gray-800">{{ $searchParams['original_start_date'] }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{t('tourbooking.return_date')}}</span>
                            <span class="font-semibold text-gray-800">{{ $searchParams['original_end_date'] }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{t('tourbooking.travellers')}}</span>
                            <span class="font-semibold text-gray-800">
                                {{ $searchParams['adult'] }} {{t('tourbooking.adults')}}
                                @if($searchParams['child'] > 0), {{ $searchParams['child'] }} {{t('tourbooking.children')}} @endif
                            </span>
                        </div>
                    </div>

                    <div class="space-y-2 mb-5 pb-5 border-b border-gray-200">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{t('tourbooking.price_per_person')}}</span>
                            <span class="font-semibold text-gray-800">{{$active_currency->currency_name}}{{ number_format(convertCurrency($tour->price, $tour->currency ?? 'USD', $active_currency->currency_name)) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{t('tourbooking.adults')}} ({{ $searchParams['adult'] }} x {{$active_currency->currency_name}}{{ number_format(convertCurrency($tour->price, $tour->currency ?? 'USD', $active_currency->currency_name)) }})</span>
                            <span class="font-semibold text-gray-800">{{$active_currency->currency_name}}{{ number_format(convertCurrency($tour->price, $tour->currency ?? 'USD', $active_currency->currency_name) * $searchParams['adult']) }}</span>
                        </div>
                        @if($searchParams['child'] > 0)
                        @php
                            $childPrice = convertCurrency($tour->child_price, $tour->currency ?? 'USD', $active_currency->currency_name) ?? (convertCurrency($tour->price, $tour->currency ?? 'USD', $active_currency->currency_name)  * 0.7);
                        @endphp
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{t('tourbooking.children')}} ({{ $searchParams['child'] }} x {{$active_currency->currency_name}}{{ number_format($childPrice) }})</span>
                            <span class="font-semibold text-gray-800">{{$active_currency->currency_name}}{{ number_format($childPrice * $searchParams['child']) }}</span>
                        </div>
                        @endif
                    </div>

                    @php
                        $childPrice = $tour->child_price ?? (convertCurrency($tour->price, $tour->currency ?? 'USD', $active_currency->currency_name)  * 0.7);
                        $totalPrice = (convertCurrency($tour->price, $tour->currency ?? 'USD', $active_currency->currency_name)  * $searchParams['adult']) + ($childPrice * $searchParams['child']);
                    @endphp
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-bold text-gray-800">{{t('tourbooking.total_amount')}}</span>
                        <span class="text-2xl font-bold" style="color: #0077BE;">{{$active_currency->currency_name}}{{ number_format($totalPrice) }}</span>
                    </div>

                    <div class="mt-4 text-center">
                        <p class="text-xs text-gray-500 flex items-center justify-center gap-1">
                            <i class="fas fa-lock"></i>
                            {{t('tourbooking.secure_payment')}}
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
