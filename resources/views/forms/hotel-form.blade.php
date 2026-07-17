{{-- Hotel Search Form Component --}}

<style>
    .flatpickr-input {
        background: transparent !important;
        border: none !important;
        cursor: pointer;
    }

    .destination-dropdown,
    .traveler-dropdown,
    .country-dropdown {
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
        min-width: 280px;
    }

    .destination-dropdown.active,
    .traveler-dropdown.active,
    .country-dropdown.active {
        display: block;
    }

    .destination-search,
    .country-search {
        width: 100%;
        padding: 8px 12px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 13px;
        line-height: 1.4;
        outline: none;
        margin-bottom: 0.75rem;
    }

    .destination-search:focus,
    .country-search:focus {
        border-color: #0077BE;
    }

    .destination-item,
    .country-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 8px 10px;
        cursor: pointer;
        border-radius: 0.5rem;
        transition: background 0.2s;
    }

    .destination-item:hover,
    .country-item:hover {
        background: #f3f4f6;
    }

    .destination-icon,
    .country-flag {
        font-size: 18px;
        width: 24px;
        text-align: center;
    }

    .destination-details {
        flex: 1;
    }

    .destination-name,
    .country-name {
        font-weight: 600;
        color: #111827;
        margin-bottom: 0.15rem;
        font-size: 13px;
    }

    .destination-location {
        font-size: 11px;
        color: #6b7280;
    }

    .location-wrapper {
        position: relative;
    }

    .traveler-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        margin-top: 0.5rem;
        width: 100%;
        max-width: 320px;
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

    .loading {
        text-align: center;
        padding: 1rem;
        color: #6b7280;
    }

    @media (max-width: 1024px) {
        .destination-dropdown,
        .country-dropdown {
            position: fixed;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            max-width: 380px;
        }

        .traveler-dropdown {
            position: fixed;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            max-width: 320px;
        }
    }
</style>

<div class="p-3 sm:p-5">
    <form id="hotelSearchForm" onsubmit="return submitHotelForm(event)">
        <div class="flex flex-col gap-2">
            <!-- Input Fields Row -->
            <div class="flex flex-col sm:flex-row items-stretch gap-2">
                <!-- Destination -->
                <div class="w-full sm:flex-1 bg-gray-50 rounded-lg p-2 location-wrapper border border-gray-200">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-map-marker-alt text-gray-400 text-[14px] mt-0.5"></i>
                        <div class="flex-1">
                            <label class="text-[10px] text-gray-500 block mb-0.5 font-medium uppercase tracking-wide">{{t('hotel.destination')}}</label>
                            <div class="text-gray-900 font-semibold text-[13px] cursor-pointer" id="hotelDestinationBtn">
                                <span id="hotelDestinationDisplay">{{t('hotel.defaultDestination')}}</span>
                            </div>
                            <input type="hidden" id="hotelDestinationValue" value="Dubai">
                        </div>
                    </div>

                    <!-- Hotel Destination Dropdown -->
                    <div id="hotelDestinationDropdown" class="destination-dropdown">
                        <input type="text" id="hotelDestinationSearch" class="destination-search" placeholder="{{t('hotel.searchDestination')}}">
                        <div id="hotelDestinationList">
                            <div class="loading">{{t('hotel.typeToSearch')}}</div>
                        </div>
                    </div>
                </div>

                <!-- Check-in Date -->
                <div class="w-full sm:flex-[0.8] bg-gray-50 rounded-lg p-2 border border-gray-200">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-calendar-alt text-gray-400 text-[14px] mt-0.5"></i>
                        <div class="flex-1">
                            <label class="text-[10px] text-gray-500 block mb-0.5 font-medium uppercase tracking-wide">{{t('hotel.checkin')}}</label>
                            <input type="text" id="hotelCheckinDate" class="w-full text-gray-900 font-semibold text-[13px] focus:outline-none cursor-pointer bg-transparent" value="<?= date('d-m-Y', strtotime('+2 days')) ?>" readonly>
                        </div>
                    </div>
                </div>

                <!-- Check-out Date -->
                <div class="w-full sm:flex-[0.8] bg-gray-50 rounded-lg p-2 border border-gray-200">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-calendar-alt text-gray-400 text-[14px] mt-0.5"></i>
                        <div class="flex-1">
                            <label class="text-[10px] text-gray-500 block mb-0.5 font-medium uppercase tracking-wide">{{t('hotel.checkout')}}</label>
                            <input type="text" id="hotelCheckoutDate" class="w-full text-gray-900 font-semibold text-[13px] focus:outline-none cursor-pointer bg-transparent" value="<?= date('d-m-Y', strtotime('+4 days')) ?>" readonly>
                        </div>
                    </div>
                </div>

                <!-- Travelers -->
                <div class="w-full sm:flex-[1.3] bg-gray-50 rounded-lg p-2 relative border border-gray-200">
                    <div class="flex items-start gap-2 cursor-pointer" id="hotelTravelerBtn">
                        <i class="fas fa-user text-gray-400 text-[14px] mt-0.5"></i>
                        <div class="flex-1">
                            <label class="text-[10px] text-gray-500 block mb-0.5 font-medium uppercase tracking-wide">{{t('hotel.travelers')}}</label>
                            <div class="text-gray-900 font-semibold text-[13px]">
                                <span id="hotelTravelerDisplay">{{t('hotel.travelersDisplay')}}</span>
                                <i class="fas fa-chevron-down text-gray-400 text-[14px] ml-1"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Traveler Dropdown -->
                    <div id="hotelTravelerDropdown" class="traveler-dropdown">
                        <h3 class="font-bold text-sm mb-3">{{t('hotel.travelersRooms')}}</h3>

                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <div class="font-semibold text-[13px]">{{t('hotel.adult')}}</div>
                                <div class="text-[11px] text-gray-500">{{t('hotel.adultAge')}}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" class="traveler-btn w-7 h-7 rounded-full border-2 border-gray-300 flex items-center justify-center hover:bg-gray-100" data-type="adult" data-action="minus">
                                    <i class="fas fa-minus text-gray-600 text-[10px]"></i>
                                </button>
                                <span id="hotelAdultCount" class="w-6 text-center font-semibold text-[13px]">2</span>
                                <button type="button" class="traveler-btn w-7 h-7 rounded-full border-2 border-[#0077BE] bg-[#E6F3FB] flex items-center justify-center" data-type="adult" data-action="plus">
                                    <i class="fas fa-plus text-[#0077BE] text-[10px]"></i>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <div class="font-semibold text-[13px]">{{t('hotel.child')}}</div>
                                <div class="text-[11px] text-gray-500">{{t('hotel.childAge')}}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" class="traveler-btn w-7 h-7 rounded-full border-2 border-gray-300 flex items-center justify-center hover:bg-gray-100" data-type="child" data-action="minus">
                                    <i class="fas fa-minus text-gray-600 text-[10px]"></i>
                                </button>
                                <span id="hotelChildCount" class="w-6 text-center font-semibold text-[13px]">0</span>
                                <button type="button" class="traveler-btn w-7 h-7 rounded-full border-2 border-[#0077BE] bg-[#E6F3FB] flex items-center justify-center" data-type="child" data-action="plus">
                                    <i class="fas fa-plus text-[#0077BE] text-[10px]"></i>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <div class="font-semibold text-[13px]">{{t('hotel.rooms')}}</div>
                                <div class="text-[11px] text-gray-500">{{t('hotel.howMany')}}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" class="traveler-btn w-7 h-7 rounded-full border-2 border-gray-300 flex items-center justify-center hover:bg-gray-100" data-type="room" data-action="minus">
                                    <i class="fas fa-minus text-gray-600 text-[10px]"></i>
                                </button>
                                <span id="hotelRoomCount" class="w-6 text-center font-semibold text-[13px]">1</span>
                                <button type="button" class="traveler-btn w-7 h-7 rounded-full border-2 border-[#0077BE] bg-[#E6F3FB] flex items-center justify-center" data-type="room" data-action="plus">
                                    <i class="fas fa-plus text-[#0077BE] text-[10px]"></i>
                                </button>
                            </div>
                        </div>

                        <button type="button" id="hotelApplyTravelerBtn" class="w-full py-2.5 bg-[#0077BE] text-white rounded-xl font-semibold text-[13px] hover:bg-[#005f99] transition-all">
                            {{t('hotel.apply')}}
                        </button>
                    </div>
                </div>

                <!-- Nationality -->
                <div class="w-full sm:flex-1 bg-gray-50 rounded-lg p-2 relative border border-gray-200">
                    <div class="flex items-start gap-2 cursor-pointer" id="hotelNationalityBtn">
                        <i class="fas fa-flag text-gray-400 text-[14px] mt-0.5"></i>
                        <div class="flex-1">
                            <label class="text-[10px] text-gray-500 block mb-0.5 font-medium uppercase tracking-wide">{{t('hotel.nationality')}}</label>
                            <div class="text-gray-900 font-semibold text-[13px]">
                                <span id="hotelNationalityDisplay">🇵🇰 {{t('hotel.defaultNationality')}}</span>
                                <i class="fas fa-chevron-down text-gray-400 text-[14px] ml-1"></i>
                            </div>
                            <input type="hidden" id="hotelNationalityValue" value="PK">
                        </div>
                    </div>

                    <!-- Country Dropdown -->
                    <div id="hotelCountryDropdown" class="country-dropdown">
                        <input type="text" id="hotelCountrySearch" class="country-search" placeholder="{{t('hotel.searchCountry')}}">
                        <div id="hotelCountryList">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Button -->
            <button type="submit" id="hotelSearchBtn" class="w-full py-2.5 px-6 bg-[#0077BE] text-white rounded-lg font-bold text-sm hover:bg-[#005f99] transition-all shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                {{t('hotel.searchHotels')}}
                <i class="fas fa-search text-xs"></i>
            </button>
        </div>
    </form>
</div>

<!-- Overlay for mobile dropdowns -->
<div id="dropdownOverlay" class="dropdown-overlay"></div>
