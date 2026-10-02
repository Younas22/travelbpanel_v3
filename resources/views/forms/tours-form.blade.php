{{-- Tours Search Form Component --}}
@php
    $tour_search = session('tour_search');
@endphp

<style>
    .flatpickr-input {
        background: transparent !important;
        border: none !important;
        cursor: pointer;
    }
 
    .destination-dropdown,
    .tour-type-dropdown,
    .traveler-dropdown {
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
        z-index: 150;
        max-height: 350px;
        overflow-y: auto;
        width: 100%;
        min-width: 280px;
    }

    .destination-dropdown.active,
    .tour-type-dropdown.active,
    .traveler-dropdown.active {
        display: block;
    }

    .traveler-dropdown {
        max-width: 320px;
    }

    .destination-search {
        width: 100%;
        padding: 8px 12px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 13px;
        line-height: 1.4;
        outline: none;
        margin-bottom: 0.75rem;
    }

    .destination-search:focus {
        border-color: #0346FA;
    }

    .destination-item,
    .tour-type-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 8px 10px;
        cursor: pointer;
        border-radius: 0.5rem;
        transition: background 0.2s;
    }

    .destination-item:hover,
    .tour-type-item:hover {
        background: #f3f4f6;
    }

    .destination-icon {
        font-size: 18px;
        width: 24px;
        text-align: center;
    }

    .destination-details {
        flex: 1;
    }

    .destination-name,
    .tour-type-name {
        font-weight: 600;
        color: #111827;
        margin-bottom: 0.15rem;
        font-size: 13px;
    }

    .destination-type {
        font-size: 11px;
        color: #6b7280;
    }

    .location-wrapper {
        position: relative;
    }

    .dropdown-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 140;
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
        .tour-type-dropdown {
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

    .destination-section-title {
        font-size: 0.75rem;
        font-weight: 700;
        color: #0346FA;
        text-transform: uppercase;
        margin-top: 0.75rem;
        margin-bottom: 0.5rem;
        padding: 0 0.75rem;
        letter-spacing: 0.5px;
    }

    .destination-section-title:first-child {
        margin-top: 0;
    }
</style>

<div class="p-3 sm:p-5">
    <form id="toursSearchForm" onsubmit="return submitToursForm(event)">
        <div class="flex flex-col gap-2">
            <!-- Input Fields Row -->
            <div class="flex flex-col sm:flex-row items-stretch gap-2">
                <!-- Where (Destination) -->
                <div class="w-full sm:flex-[1.2] bg-gray-50 rounded-lg p-2 location-wrapper border border-gray-200">
                    <div class="flex items-start gap-2 cursor-pointer" id="toursDestinationBtn">
                        <i class="fas fa-map-marker-alt text-gray-400 text-[14px] mt-0.5"></i>
                        <div class="flex-1">
                            <label class="text-[10px] text-gray-500 block mb-0.5 font-medium uppercase tracking-wide">{{t('tourform.where')}}</label>
                            <div class="text-gray-900 font-semibold text-[13px]">
                                <span id="toursDestinationDisplay">{{ isset($tour_search['location_name']) && $tour_search['location_name'] ? $tour_search['location_name'] . ($tour_search['location_country'] ? ', ' . $tour_search['location_country'] : '') : 'Dubai, United Arab Emirates' }}</span>
                                <i class="fas fa-chevron-down text-gray-400 text-[14px] ml-1"></i>
                            </div>
                            <input type="hidden" id="toursDestinationValue" value="{{ isset($tour_search['location']) && $tour_search['location'] ? $tour_search['location'] : 'Dubai' }}">
                        </div>
                    </div>

                    <!-- Destination Dropdown -->
                    <div id="toursDestinationDropdown" class="destination-dropdown">
                        <input type="text" id="toursDestinationSearch" class="destination-search" placeholder="{{t('tourform.search_destinations')}}...">
                        <div id="toursDestinationList">
                            <div class="loading">{{t('tourform.type_3_characters')}}</div>
                        </div>
                    </div>
                </div>

                <!-- When (Date) -->
                <div class="w-full sm:flex-[1.2] bg-gray-50 rounded-lg p-2 border border-gray-200">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-calendar-alt text-gray-400 text-[14px] mt-0.5"></i>
                        <div class="flex-1">
                            <label class="text-[10px] text-gray-500 block mb-0.5 font-medium uppercase tracking-wide">{{t('tourform.when')}}</label>
                            <input type="text" id="toursDate" class="w-full text-gray-900 font-semibold text-[13px] focus:outline-none cursor-pointer bg-transparent" value="{{ isset($tour_search['original_start_date']) && $tour_search['original_start_date'] ? $tour_search['original_start_date'] . ' to ' . $tour_search['original_end_date'] : '' }}" readonly>
                        </div>
                    </div>
                </div>

                <!-- Tour Type -->
                <div class="w-full sm:flex-[0.8] bg-gray-50 rounded-lg p-2 relative border border-gray-200">
                    <div class="flex items-start gap-2 cursor-pointer" id="tourTypeBtn">
                        <i class="fas fa-tag text-gray-400 text-[14px] mt-0.5"></i>
                        <div class="flex-1">
                            <label class="text-[10px] text-gray-500 block mb-0.5 font-medium uppercase tracking-wide">{{t('tourform.tour_type')}}</label>
                            <div class="text-gray-900 font-semibold text-[13px]">
                                <span id="tourTypeDisplay">{{ isset($tour_search['type_name']) && $tour_search['type_name'] ? $tour_search['type_name'] : t('tourform.all_tour') }}</span>
                                <i class="fas fa-chevron-down text-gray-400 text-[14px] ml-1"></i>
                            </div>
                            <input type="hidden" id="tourTypeValue" value="{{ isset($tour_search['type']) && $tour_search['type'] ? $tour_search['type'] : 'all' }}">
                        </div>
                    </div>

                    <!-- Tour Type Dropdown -->
                    <div id="tourTypeDropdown" class="tour-type-dropdown">
                        <div id="tourTypeList">
                            <div class="loading">{{t('tourform.loading_tour_types')}}</div>
                        </div>
                    </div>
                </div>

                <!-- Travelers -->
                <div class="w-full sm:flex-[0.8] bg-gray-50 rounded-lg p-2 relative border border-gray-200">
                    <div class="flex items-start gap-2 cursor-pointer" id="toursTravelerBtn">
                        <i class="fas fa-user text-gray-400 text-[14px] mt-0.5"></i>
                        <div class="flex-1">
                            <label class="text-[10px] text-gray-500 block mb-0.5 font-medium uppercase tracking-wide">{{t('tourform.travelers')}}</label>
                            <div class="text-gray-900 font-semibold text-[13px]">
                                <span id="toursTravelerDisplay">{{ isset($tour_search['adult']) ? $tour_search['adult'] : 2 }} {{t('tourform.adult')}}, {{ isset($tour_search['child']) ? $tour_search['child'] : 0 }} {{t('tourform.child')}}</span>
                                <i class="fas fa-chevron-down text-gray-400 text-[14px] ml-1"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Traveler Dropdown -->
                    <div id="toursTravelerDropdown" class="traveler-dropdown">
                        <h3 class="font-bold text-sm mb-3">{{t('tourform.travelers')}}</h3>

                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <div class="font-semibold text-[13px]">{{t('tourform.adult')}}</div>
                                <div class="text-[11px] text-gray-500">{{t('tourform.years_12_plus')}}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" class="tours-traveler-btn w-7 h-7 rounded-full border-2 border-gray-300 flex items-center justify-center hover:bg-gray-100" data-type="adult" data-action="minus">
                                    <i class="fas fa-minus text-gray-600 text-[10px]"></i>
                                </button>
                                <span id="toursAdultCount" class="w-6 text-center font-semibold text-[13px]">{{ isset($tour_search['adult']) ? $tour_search['adult'] : 2 }}</span>
                                <button type="button" class="tours-traveler-btn w-7 h-7 rounded-full border-2 border-[#0346FA] bg-[#E6F3FB] flex items-center justify-center" data-type="adult" data-action="plus">
                                    <i class="fas fa-plus text-[#0346FA] text-[10px]"></i>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <div class="font-semibold text-[13px]">{{t('tourform.child')}}</div>
                                <div class="text-[11px] text-gray-500">{{t('tourform.years_2_11')}}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" class="tours-traveler-btn w-7 h-7 rounded-full border-2 border-gray-300 flex items-center justify-center hover:bg-gray-100" data-type="child" data-action="minus">
                                    <i class="fas fa-minus text-gray-600 text-[10px]"></i>
                                </button>
                                <span id="toursChildCount" class="w-6 text-center font-semibold text-[13px]">{{ isset($tour_search['child']) ? $tour_search['child'] : 0 }}</span>
                                <button type="button" class="tours-traveler-btn w-7 h-7 rounded-full border-2 border-[#0346FA] bg-[#E6F3FB] flex items-center justify-center" data-type="child" data-action="plus">
                                    <i class="fas fa-plus text-[#0346FA] text-[10px]"></i>
                                </button>
                            </div>
                        </div>

                        <button type="button" id="toursApplyTravelerBtn" class="w-full py-2.5 bg-[#0346FA] text-white rounded-xl font-semibold text-[13px] hover:bg-[#005f99] transition-all">
                            {{t('tourform.apply')}}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Search Button -->
            <button type="submit" id="toursSearchBtn" class="w-full py-2.5 px-6 bg-[#0346FA] text-white rounded-lg font-bold text-sm hover:bg-[#005f99] transition-all shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                {{t('tourform.search_tours')}}
                <i class="fas fa-search text-xs"></i>
            </button>
        </div>
    </form>
</div>

<!-- Overlay for mobile dropdowns -->
<div id="toursDropdownOverlay" class="dropdown-overlay"></div>
