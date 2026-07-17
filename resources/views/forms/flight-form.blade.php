{{-- Flight Search Form Component - FIXED VERSION --}}
@php
    $flight_search = session('flight_search');
@endphp

<style>
    .flatpickr-input {
        background: transparent !important;
        border: none !important;
        cursor: pointer;
    }

    .airport-dropdown,
    .passenger-dropdown,
    .class-dropdown {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        margin-top: 0.5rem;
        background: white;
        border-radius: 1rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        padding: 1rem;
        z-index: 100;
        max-height: 350px;
        overflow-y: auto;
        width: 100%;
    }

    .airport-dropdown.active,
    .passenger-dropdown.active,
    .class-dropdown.active {
        display: block;
    }

    .airport-search {
        width: 100%;
        padding: 8px 12px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 13px;
        line-height: 1.4;
        outline: none;
        margin-bottom: 0.75rem;
    }

    .airport-search:focus {
        border-color: #0077BE;
    }

    .airport-item,
    .class-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 8px 10px;
        cursor: pointer;
        border-radius: 0.5rem;
        transition: background 0.2s;
    }

    .airport-item:hover,
    .class-item:hover {
        background: #f3f4f6;
    }

    .airport-icon {
        color: #6b7280;
        font-size: 18px;
        width: 24px;
        text-align: center;
    }

    .airport-details {
        flex: 1;
    }

    .airport-name,
    .class-name {
        font-weight: 600;
        color: #111827;
        margin-bottom: 0.15rem;
        font-size: 13px;
    }

    .airport-code {
        font-size: 11px;
        color: #6b7280;
    }

    .location-wrapper {
        position: relative;
    }

    .passenger-dropdown,
    .class-dropdown {
        min-width: 320px;
    }

    .dropdown-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 40;
    }

    .dropdown-overlay.active {
        display: block;
    }

    @media (max-width: 1024px) {
        .passenger-dropdown,
        .class-dropdown {
            position: fixed;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            max-width: 320px;
        }
    }

    .trip-type-btn {
        background: #f3f4f6;
        color: #6b7280;
        border: 2px solid transparent;
        padding: 6px 12px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
        font-size: 13px;
    }

    .trip-type-btn:hover {
        background: #e5e7eb;
    }

    .trip-type-btn.active {
        background: #E6F3FB;
        color: #0077BE;
        border-color: #0077BE;
    }

    .loading {
        text-align: center;
        padding: 1rem;
        color: #6b7280;
    }

    @media (min-width: 1025px) {
        .airport-dropdown {
            min-width: 420px;
        }
    }

    @media (max-width: 1024px) {
        .airport-dropdown {
            position: fixed;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            max-width: 450px;
        }
    }
</style>

<div class="p-3 sm:p-5">
    <form id="flightSearchForm" onsubmit="return submitFlightForm(event)">
        <div class="flex flex-col gap-2">
            <!-- Trip Type Toggle -->
            <div class="flex gap-2 mb-2">
                <button type="button" id="roundTripBtn" class="trip-type-btn @if(isset($flight_search['trip_type']) && $flight_search['trip_type'] == 'round') active @endif">
                    <i class="fas fa-exchange-alt mr-2"></i>{{t('flightform.round_trip')}}
                </button>
                <button type="button" id="oneWayBtn" class="trip-type-btn @if(session('flight_search.trip_type') == 'oneway') active @elseif(!session('flight_search.trip_type')) active @endif">
                    <i class="fas fa-long-arrow-alt-right mr-2"></i>{{t('flightform.one_way')}}
                </button>
            </div>

            <!-- Input Fields Row -->
            <div class="flex flex-col sm:flex-row items-stretch gap-2">
                <!-- From -->
                <div class="w-full sm:flex-1 bg-gray-50 rounded-lg p-2 location-wrapper border border-gray-200">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-plane-departure text-gray-400 text-[14px] mt-0.5"></i>
                        <div class="flex-1">
                            <label class="text-[10px] text-gray-500 block mb-0.5 font-medium uppercase tracking-wide">{{t('flightform.from')}}</label>
                            <div class="text-gray-900 font-semibold text-[13px] cursor-pointer" id="flightFromBtn">
                                <span id="flightFromDisplay">{{ isset($flight_search['origin_name']) && $flight_search['origin_name'] ? $flight_search['origin_name'] . ' (' . $flight_search['origin'] . ')' : t('flightform.from') }}</span>
                            </div>
                            <input type="hidden" id="flightFromValue" value="{{ isset($flight_search['origin']) && $flight_search['origin'] ? $flight_search['origin'] : 'lhe' }}">
                        </div>
                    </div>

                    <!-- From Airport Dropdown -->
                    <div id="flightFromDropdown" class="airport-dropdown">
                        <input type="text" id="flightFromSearch" class="airport-search" placeholder="{{t('flightform.from_placeholder')}}">
                        <div id="flightFromList">
                            <div class="loading">{{t('flightform.from_placeholder')}}</div>
                        </div>
                    </div>
                </div>

                <!-- To -->
                <div class="w-full sm:flex-1 bg-gray-50 rounded-lg p-2 location-wrapper border border-gray-200">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-plane-arrival text-gray-400 text-[14px] mt-0.5"></i>
                        <div class="flex-1">
                            <label class="text-[10px] text-gray-500 block mb-0.5 font-medium uppercase tracking-wide">{{t('flightform.to')}}</label>
                            <div class="text-gray-900 font-semibold text-[13px] cursor-pointer" id="flightToBtn">
                                <span id="flightToDisplay">{{ isset($flight_search['destination_name']) && $flight_search['destination_name'] ? $flight_search['destination_name'] . ' (' . $flight_search['destination'] . ')' : t('flightform.to') }}</span>
                            </div>
                            <input type="hidden" id="flightToValue" value="{{ isset($flight_search['destination']) && $flight_search['destination'] ? $flight_search['destination'] : 'dxb' }}">
                        </div>
                    </div>

                    <!-- To Airport Dropdown -->
                    <div id="flightToDropdown" class="airport-dropdown">
                        <input type="text" id="flightToSearch" class="airport-search" placeholder="{{t('flightform.from_placeholder')}}">
                        <div id="flightToList">
                            <div class="loading">{{t('flightform.from_placeholder')}}</div>
                        </div>
                    </div>
                </div>

                <!-- Departure Date -->
                <div class="w-full sm:flex-1 bg-gray-50 rounded-lg p-2 border border-gray-200">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-calendar-alt text-gray-400 text-[14px] mt-0.5"></i>
                        <div class="flex-1">
                            <label class="text-[10px] text-gray-500 block mb-0.5 font-medium uppercase tracking-wide">{{t('flightform.departure')}}</label>
                            <input type="text" id="flightDepartureDate" class="w-full text-gray-900 font-semibold text-[13px] focus:outline-none cursor-pointer bg-transparent" readonly>
                        </div>
                    </div>
                </div>

                <!-- Return Date -->
                <div id="returnDateField" class="w-full sm:flex-1 bg-gray-50 rounded-lg p-2 border border-gray-200 {{ empty($flight_search['return_date']) ? 'hidden' : '' }}">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-calendar-alt text-gray-400 text-[14px] mt-0.5"></i>
                        <div class="flex-1">
                            <label class="text-[10px] text-gray-500 block mb-0.5 font-medium uppercase tracking-wide">{{t('flightform.return')}}</label>
                            <input type="text" id="flightReturnDate" class="w-full text-gray-900 font-semibold text-[13px] focus:outline-none cursor-pointer bg-transparent" readonly>
                        </div>
                    </div>
                </div>

                <!-- Passengers -->
                <div class="w-full sm:flex-1 bg-gray-50 rounded-lg p-2 relative border border-gray-200">
                    <div class="flex items-start gap-2 cursor-pointer" id="flightPassengerBtn">
                        <i class="fas fa-user text-gray-400 text-[14px] mt-0.5"></i>
                        <div class="flex-1">
                            <label class="text-[10px] text-gray-500 block mb-0.5 font-medium uppercase tracking-wide">{{t('flightform.passengers')}}</label>
                            <div class="text-gray-900 font-semibold text-[13px]">
                                <span id="flightPassengerDisplay">{{ isset($flight_search['passenger_count']) && $flight_search['passenger_count'] ? $flight_search['passenger_count'] : 1 }} Passenger</span>
                                <i class="fas fa-chevron-down text-gray-400 text-[14px] ml-1"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Passenger Dropdown -->
                    <div id="flightPassengerDropdown" class="passenger-dropdown">
                        <h3 class="font-bold text-sm mb-3">{{t('flightform.passengers')}}</h3>

                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <div class="font-semibold text-[13px]">{{t('flightform.adults')}}</div>
                                <div class="text-[11px] text-gray-500">(12+{{t('flightform.years')}})</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" class="passenger-btn w-7 h-7 rounded-full border-2 border-gray-300 flex items-center justify-center hover:bg-gray-100" data-type="adult" data-action="minus">
                                    <i class="fas fa-minus text-gray-600 text-[10px]"></i>
                                </button>
                                <span id="flightAdultCount" class="w-6 text-center font-semibold text-[13px]">{{ isset($flight_search['adult']) && $flight_search['adult'] ? $flight_search['adult'] : 1 }}</span>
                                <button type="button" class="passenger-btn w-7 h-7 rounded-full border-2 border-[#0077BE] bg-[#E6F3FB] flex items-center justify-center" data-type="adult" data-action="plus">
                                    <i class="fas fa-plus text-[#0077BE] text-[10px]"></i>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <div class="font-semibold text-[13px]">{{t('flightform.children')}}</div>
                                <div class="text-[11px] text-gray-500">(2-11 {{t('flightform.years')}})</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" class="passenger-btn w-7 h-7 rounded-full border-2 border-gray-300 flex items-center justify-center hover:bg-gray-100" data-type="child" data-action="minus">
                                    <i class="fas fa-minus text-gray-600 text-[10px]"></i>
                                </button>
                                <span id="flightChildCount" class="w-6 text-center font-semibold text-[13px]">{{ isset($flight_search['children']) && $flight_search['children'] ? $flight_search['children'] : 0 }}</span>
                                <button type="button" class="passenger-btn w-7 h-7 rounded-full border-2 border-[#0077BE] bg-[#E6F3FB] flex items-center justify-center" data-type="child" data-action="plus">
                                    <i class="fas fa-plus text-[#0077BE] text-[10px]"></i>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <div class="font-semibold text-[13px]">{{t('flightform.infants')}}</div>
                                <div class="text-[11px] text-gray-500">(Under 2 {{t('flightform.years')}})</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" class="passenger-btn w-7 h-7 rounded-full border-2 border-gray-300 flex items-center justify-center hover:bg-gray-100" data-type="infant" data-action="minus">
                                    <i class="fas fa-minus text-gray-600 text-[10px]"></i>
                                </button>
                                <span id="flightInfantCount" class="w-6 text-center font-semibold text-[13px]">{{ isset($flight_search['infants']) && $flight_search['infants'] ? $flight_search['infants'] : 0 }}</span>
                                <button type="button" class="passenger-btn w-7 h-7 rounded-full border-2 border-[#0077BE] bg-[#E6F3FB] flex items-center justify-center" data-type="infant" data-action="plus">
                                    <i class="fas fa-plus text-[#0077BE] text-[10px]"></i>
                                </button>
                            </div>
                        </div>

                        <button type="button" id="flightApplyPassengerBtn" class="w-full py-2.5 bg-[#0077BE] text-white rounded-xl font-semibold text-[13px] hover:bg-[#005f99] transition-all">
                            {{t('flightform.apply')}}
                        </button>
                    </div>
                </div>

                <!-- Class -->
                <div class="w-full sm:flex-1 bg-gray-50 rounded-lg p-2 relative border border-gray-200">
                    <div class="flex items-start gap-2 cursor-pointer" id="flightClassBtn">
                        <i class="fas fa-chair text-gray-400 text-[14px] mt-0.5"></i>
                        <div class="flex-1">
                            <label class="text-[10px] text-gray-500 block mb-0.5 font-medium uppercase tracking-wide">{{t('flightform.class')}}</label>
                            <div class="text-gray-900 font-semibold text-[13px]">
                                <span id="flightClassDisplay">{{t('flightform.economy')}}</span>
                                <i class="fas fa-chevron-down text-gray-400 text-[14px] ml-1"></i>
                            </div>
                            <input type="hidden" id="flightClassValue" value="{{ isset($flight_search['flight_type']) && $flight_search['flight_type'] ? ($flight_search['flight_type']) : 'economy'}}">
                        </div>
                    </div>

                    <!-- Class Dropdown -->
                    <div id="flightClassDropdown" class="class-dropdown">
                        <div id="flightClassList">
                            <div class="class-item" data-class="economy">
                                <i class="fas fa-chair text-gray-500"></i>
                                <span class="class-name">{{t('flightform.economy')}}</span>
                            </div>
                            <div class="class-item" data-class="premium_economy">
                                <i class="fas fa-chair text-gray-500"></i>
                                <span class="class-name">{{t('flightform.premium_economy')}}</span>
                            </div>
                            <div class="class-item" data-class="business">
                                <i class="fas fa-chair text-gray-500"></i>
                                <span class="class-name">{{t('flightform.business')}}</span>
                            </div>
                            <div class="class-item" data-class="first">
                                <i class="fas fa-chair text-gray-500"></i>
                                <span class="class-name">{{t('flightform.first_class')}}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Button -->
            <button type="submit" id="flightSearchBtn" class="w-full py-2.5 px-6 bg-[#0077BE] text-white rounded-lg font-bold text-sm hover:bg-[#005f99] transition-all shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                {{t('flightform.search_flights')}}
                <i class="fas fa-search text-xs"></i>
            </button>
        </div>
    </form>
</div>

<!-- Overlay for mobile dropdowns -->
<div id="flightDropdownOverlay" class="dropdown-overlay"></div>
