{{-- Umrah Search Form Component --}}
@php
    $umrah_search = session('umrah_search');
@endphp

<style>
    .flatpickr-input {
        background: transparent !important;
        border: none !important;
        cursor: pointer;
    }

    .airport-dropdown,
    .passenger-dropdown {
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
        z-index: 1000;
        max-height: 350px;
        overflow-y: auto;
        width: 100%;
        border: 1px solid #e5e7eb;
    }

    .airport-dropdown.active,
    .passenger-dropdown.active {
        display: block;
    }

    .airport-search {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        font-size: 0.8125rem;
        outline: none;
        margin-bottom: 0.75rem;
        background: white;
        color: #111827;
    }

    .airport-search:focus {
        border-color: #0346FA;
    }

    .airport-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.5rem 0.625rem;
        cursor: pointer;
        border-radius: 0.5rem;
        transition: background 0.2s;
    }

    .airport-item:hover {
        background: #f3f4f6;
    }

    .airport-icon {
        color: #6b7280;
        font-size: 1.125rem;
        width: 24px;
        text-align: center;
    }

    .airport-details {
        flex: 1;
    }

    .airport-name {
        font-weight: 600;
        color: #111827;
        margin-bottom: 0.15rem;
        font-size: 0.8125rem;
    }

    .airport-code {
        font-size: 0.6875rem;
        color: #6b7280;
    }

    .airport-wrapper {
        position: relative;
        overflow: visible !important;
    }

    .passenger-dropdown {
        min-width: 320px;
        z-index: 1000;
    }

    .dropdown-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
    }

    .dropdown-overlay.active {
        display: block;
    }

    .loading {
        text-align: center;
        padding: 1rem;
        color: #6b7280;
    }

    /* Desktop - Airport dropdown sizing */
    @media (min-width: 1025px) {
        .airport-dropdown {
            min-width: 420px;
        }
    }

    /* Tablet and Mobile - Full width dropdown */
    @media (max-width: 1024px) {
        .airport-dropdown {
            position: fixed !important;
            width: 90% !important;
            max-width: 450px;
            left: 50% !important;
            top: 50% !important;
            transform: translate(-50%, -50%) !important;
            z-index: 1000 !important;
        }

        .passenger-dropdown {
            position: fixed !important;
            width: 90% !important;
            max-width: 320px;
            left: 50% !important;
            top: 50% !important;
            transform: translate(-50%, -50%) !important;
            z-index: 1000 !important;
        }
    }

    .nights-counter {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .nights-btn {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        border: 2px solid #0346FA;
        background: #E6F3FB;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }

    .nights-btn:hover {
        background: #0346FA;
        color: white;
    }

    .nights-btn.minus {
        border-color: #d1d5db;
        background: white;
    }

    .nights-btn.minus:hover {
        background: #f3f4f6;
        color: #111827;
    }

    .nights-value {
        min-width: 30px;
        text-align: center;
        font-weight: 600;
        font-size: 0.8125rem;
    }
</style>

<div class="p-3 sm:p-5">
    <form id="umrahSearchForm" onsubmit="return submitUmrahForm(event)">
        <div class="flex flex-col gap-2">
            <!-- Input Fields Row -->
            <div class="flex flex-col sm:flex-row items-stretch gap-2">
                <!-- From -->
                <div class="w-full sm:flex-1 bg-gray-50 rounded-lg p-2 airport-wrapper border border-gray-200">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-map-marker-alt text-gray-400 text-[14px] mt-0.5"></i>
                        <div class="flex-1">
                            <label class="text-[10px] font-medium text-gray-500 block mb-0.5 uppercase tracking-wide">{{t('umrahform.from')}}</label>
                            <div class="text-gray-900 font-semibold text-[13px] cursor-pointer" id="umrahFromBtn">
                                <span id="umrahFromDisplay">{{ isset($umrah_search['origin_name']) && $umrah_search['origin_name'] ? $umrah_search['origin_name'] : 'Lahore (LHE)' }}</span>
                            </div>
                            <input type="hidden" id="umrahFromValue" value="{{ isset($umrah_search['origin']) && $umrah_search['origin'] ? $umrah_search['origin'] : 'LHE' }}">
                        </div>
                    </div>

                    <!-- From Airport Dropdown -->
                    <div id="umrahFromDropdown" class="airport-dropdown">
                        <input type="text" id="umrahFromSearch" class="airport-search" placeholder="{{t('umrahform.search_location')}}">
                        <div id="umrahFromList">
                            <div class="loading">{{t('umrahform.loading')}}</div>
                        </div>
                    </div>
                </div>

                <!-- To -->
                <div class="w-full sm:flex-1 bg-gray-50 rounded-lg p-2 airport-wrapper border border-gray-200">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-kaaba text-gray-400 text-[14px] mt-0.5"></i>
                        <div class="flex-1">
                            <label class="text-[10px] font-medium text-gray-500 block mb-0.5 uppercase tracking-wide">{{t('umrahform.to')}}</label>
                            <div class="text-gray-900 font-semibold text-[13px] cursor-pointer" id="umrahToBtn">
                                <span id="umrahToDisplay">{{ isset($umrah_search['destination_name']) && $umrah_search['destination_name'] ? $umrah_search['destination_name'] : 'Madinah (MED)' }}</span>
                            </div>
                            <input type="hidden" id="umrahToValue" value="{{ isset($umrah_search['destination']) && $umrah_search['destination'] ? $umrah_search['destination'] : 'MED' }}">
                        </div>
                    </div>

                    <!-- To Airport Dropdown -->
                    <div id="umrahToDropdown" class="airport-dropdown">
                        <input type="text" id="umrahToSearch" class="airport-search" placeholder="{{t('umrahform.search_location')}}">
                        <div id="umrahToList">
                            <div class="loading">{{t('umrahform.loading')}}</div>
                        </div>
                    </div>
                </div>

                <!-- Departure Date -->
                <div class="w-full sm:flex-1 bg-gray-50 rounded-lg p-2 border border-gray-200">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-calendar-alt text-gray-400 text-[14px] mt-0.5"></i>
                        <div class="flex-1">
                            <label class="text-[10px] font-medium text-gray-500 block mb-0.5 uppercase tracking-wide">{{t('umrahform.departure')}}</label>
                            <input type="text" id="umrahDepartureDate" class="w-full text-gray-900 font-semibold text-[13px] focus:outline-none cursor-pointer bg-transparent" readonly value="{{ isset($umrah_search['departure_date']) && $umrah_search['departure_date'] ? $umrah_search['departure_date'] : '' }}" placeholder="{{t('umrahform.select_date')}}">
                        </div>
                    </div>
                </div>

                <!-- Return Date -->
                <div class="w-full sm:flex-1 bg-gray-50 rounded-lg p-2 border border-gray-200">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-calendar-alt text-gray-400 text-[14px] mt-0.5"></i>
                        <div class="flex-1">
                            <label class="text-[10px] font-medium text-gray-500 block mb-0.5 uppercase tracking-wide">{{t('umrahform.return')}}</label>
                            <input type="text" id="umrahReturnDate" class="w-full text-gray-900 font-semibold text-[13px] focus:outline-none cursor-pointer bg-transparent" readonly value="{{ isset($umrah_search['return_date']) && $umrah_search['return_date'] ? $umrah_search['return_date'] : '' }}" placeholder="{{t('umrahform.select_date')}}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Second Row -->
            <div class="flex flex-col sm:flex-row items-stretch gap-2">
                <!-- Passengers -->
                <div class="w-full sm:flex-1 bg-gray-50 rounded-lg p-2 relative border border-gray-200">
                    <div class="flex items-start gap-2 cursor-pointer" id="umrahPassengerBtn">
                        <i class="fas fa-user text-gray-400 text-[14px] mt-0.5"></i>
                        <div class="flex-1">
                            <label class="text-[10px] font-medium text-gray-500 block mb-0.5 uppercase tracking-wide">{{t('umrahform.passengers')}}</label>
                            <div class="text-gray-900 font-semibold text-[13px]">
                                <span id="umrahPassengerDisplay">{{ isset($umrah_search['passenger_count']) && $umrah_search['passenger_count'] ? $umrah_search['passenger_count'] : 1 }} {{t('umrahform.passenger')}}</span>
                                <i class="fas fa-chevron-down text-gray-400 text-[14px] ml-1"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Passenger Dropdown -->
                    <div id="umrahPassengerDropdown" class="passenger-dropdown">
                        <h3 class="font-bold text-sm mb-3">{{t('umrahform.passengers')}}</h3>

                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <div class="font-semibold text-[13px]">{{t('umrahform.adults')}}</div>
                                <div class="text-[10px] text-gray-500">(12+ {{t('umrahform.years')}})</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" class="passenger-btn w-7 h-7 rounded-full border-2 border-gray-300 flex items-center justify-center hover:bg-gray-100" data-type="adult" data-action="minus">
                                    <i class="fas fa-minus text-gray-600 text-[10px]"></i>
                                </button>
                                <span id="umrahAdultCount" class="w-6 text-center font-semibold text-[13px]">{{ isset($umrah_search['adult']) && $umrah_search['adult'] ? $umrah_search['adult'] : 1 }}</span>
                                <button type="button" class="passenger-btn w-7 h-7 rounded-full border-2 border-[#0346FA] bg-[#E6F3FB] flex items-center justify-center" data-type="adult" data-action="plus">
                                    <i class="fas fa-plus text-[#0346FA] text-[10px]"></i>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <div class="font-semibold text-[13px]">{{t('umrahform.children')}}</div>
                                <div class="text-[10px] text-gray-500">(2-11 {{t('umrahform.years')}})</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" class="passenger-btn w-7 h-7 rounded-full border-2 border-gray-300 flex items-center justify-center hover:bg-gray-100" data-type="child" data-action="minus">
                                    <i class="fas fa-minus text-gray-600 text-[10px]"></i>
                                </button>
                                <span id="umrahChildCount" class="w-6 text-center font-semibold text-[13px]">{{ isset($umrah_search['children']) && $umrah_search['children'] ? $umrah_search['children'] : 0 }}</span>
                                <button type="button" class="passenger-btn w-7 h-7 rounded-full border-2 border-[#0346FA] bg-[#E6F3FB] flex items-center justify-center" data-type="child" data-action="plus">
                                    <i class="fas fa-plus text-[#0346FA] text-[10px]"></i>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <div class="font-semibold text-[13px]">{{t('umrahform.infants')}}</div>
                                <div class="text-[10px] text-gray-500">(Under 2 {{t('umrahform.years')}})</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" class="passenger-btn w-7 h-7 rounded-full border-2 border-gray-300 flex items-center justify-center hover:bg-gray-100" data-type="infant" data-action="minus">
                                    <i class="fas fa-minus text-gray-600 text-[10px]"></i>
                                </button>
                                <span id="umrahInfantCount" class="w-6 text-center font-semibold text-[13px]">{{ isset($umrah_search['infants']) && $umrah_search['infants'] ? $umrah_search['infants'] : 0 }}</span>
                                <button type="button" class="passenger-btn w-7 h-7 rounded-full border-2 border-[#0346FA] bg-[#E6F3FB] flex items-center justify-center" data-type="infant" data-action="plus">
                                    <i class="fas fa-plus text-[#0346FA] text-[10px]"></i>
                                </button>
                            </div>
                        </div>

                        <button type="button" id="umrahApplyPassengerBtn" class="w-full py-2 bg-[#0346FA] text-white rounded-xl font-semibold text-[13px] hover:bg-[#005f99] transition-all">
                            {{t('umrahform.apply')}}
                        </button>
                    </div>
                </div>

                <!-- Makkah Nights -->
                <div class="w-full sm:flex-1 bg-gray-50 rounded-lg p-2 border border-gray-200">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-moon text-gray-400 text-[14px] mt-0.5"></i>
                        <div class="flex-1">
                            <label class="text-[10px] font-medium text-gray-500 block mb-0.5 uppercase tracking-wide">{{t('umrahform.makkah_nights')}}</label>
                            <div class="nights-counter mt-1">
                                <button type="button" class="nights-btn minus" id="makkahMinus">
                                    <i class="fas fa-minus text-[10px]"></i>
                                </button>
                                <span class="nights-value" id="makkahNightsDisplay">{{ isset($umrah_search['makkah_nights']) && $umrah_search['makkah_nights'] ? $umrah_search['makkah_nights'] : 0 }}</span>
                                <button type="button" class="nights-btn" id="makkahPlus">
                                    <i class="fas fa-plus text-[10px] text-[#0346FA]"></i>
                                </button>
                                <input type="hidden" id="makkahNightsValue" value="{{ isset($umrah_search['makkah_nights']) && $umrah_search['makkah_nights'] ? $umrah_search['makkah_nights'] : 0 }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Madina Nights -->
                <div class="w-full sm:flex-1 bg-gray-50 rounded-lg p-2 border border-gray-200">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-moon text-gray-400 text-[14px] mt-0.5"></i>
                        <div class="flex-1">
                            <label class="text-[10px] font-medium text-gray-500 block mb-0.5 uppercase tracking-wide">{{t('umrahform.madina_nights')}}</label>
                            <div class="nights-counter mt-1">
                                <button type="button" class="nights-btn minus" id="madinaMinus">
                                    <i class="fas fa-minus text-[10px]"></i>
                                </button>
                                <span class="nights-value" id="madinaNightsDisplay">{{ isset($umrah_search['madina_nights']) && $umrah_search['madina_nights'] ? $umrah_search['madina_nights'] : 0 }}</span>
                                <button type="button" class="nights-btn" id="madinaPlus">
                                    <i class="fas fa-plus text-[10px] text-[#0346FA]"></i>
                                </button>
                                <input type="hidden" id="madinaNightsValue" value="{{ isset($umrah_search['madina_nights']) && $umrah_search['madina_nights'] ? $umrah_search['madina_nights'] : 0 }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Button -->
            <button type="submit" id="umrahSearchBtn" class="w-full px-6 py-2.5 bg-[#0346FA] text-white rounded-lg font-bold text-sm hover:bg-[#005f99] transition-all shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                {{t('umrahform.search_umrah')}}
                <i class="fas fa-search text-xs"></i>
            </button>
        </div>
    </form>
</div>

<!-- Overlay for mobile dropdowns -->
<div id="umrahDropdownOverlay" class="dropdown-overlay"></div>