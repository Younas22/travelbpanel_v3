@php
    $flight_search = session('flight_search');
@endphp

<div class="sleek-flight-search">
     <div class="search-header">
            <div class="nav-pills">
                <span class="pill active">Flights</span>
                <a href="{{ route('visa.create') }}" class="pill">Visa</a>
            </div>
        </div>
    <div class="glass-container">
        <!-- Main Search Form -->
        <form id="flightssearch">
            <!-- First Row: From, To, Trip Type -->
            <div class="search-row-top">
                <div class="from-to-container">
                    <div class="input-wrapper from-to-wrapper">
                        <label class="input-label">From</label>
                        <div class="city-input from-city">
                            <i class="bi bi-airplane-engines city-icon"></i>
                            <select class="city-field depart-city" id="origin">
                                <option value="@if(isset($flight_search['origin']) && $flight_search['origin']) {{ $flight_search['origin'] }} @endif" selected>
                                    {{ isset($flight_search['origin']) && $flight_search['origin'] ? $flight_search['origin'] : 'Departure city' }}
                                </option>
                                <optgroup label="Popular Destinations">
                                    <option value="New York - JFK">🗽 New York - JFK</option>
                                    <option value="London - LHR">🇬🇧 London - LHR</option>
                                    <option value="Paris - CDG">🇫🇷 Paris - CDG</option>
                                    <option value="Tokyo - NRT">🇯🇵 Tokyo - NRT</option>
                                    <option value="Dubai - DXB">🇦🇪 Dubai - DXB</option>
                                </optgroup>
                                <optgroup label="Asian Cities">
                                    <option value="Singapore - SIN">🇸🇬 Singapore - SIN</option>
                                    <option value="Bangkok - BKK">🇹🇭 Bangkok - BKK</option>
                                    <option value="Mumbai - BOM">🇮🇳 Mumbai - BOM</option>
                                    <option value="Delhi - DEL">🇮🇳 Delhi - DEL</option>
                                    <option value="Jakarta - CGK">🇮🇩 Jakarta - CGK</option>
                                </optgroup>
                                <optgroup label="Other Cities">
                                    <option value="Sydney - SYD">🇦🇺 Sydney - SYD</option>
                                    <option value="Toronto - YYZ">🇨🇦 Toronto - YYZ</option>
                                    <option value="Los Angeles - LAX">🇺🇸 Los Angeles - LAX</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>
                    
                    <div class="input-wrapper from-to-wrapper">
                        <label class="input-label">To</label>
                        <div class="city-input to-city">
                            <i class="bi bi-geo-alt city-icon"></i>
                            <select class="city-field arrival-city" id="destination">
                                <option value="@if(isset($flight_search['destination']) && $flight_search['destination']) {{ $flight_search['destination'] }} @endif" selected>
                                    {{ isset($flight_search['destination']) && $flight_search['destination'] ? $flight_search['destination'] : 'Destination city' }}
                                </option>
                                <optgroup label="Popular Destinations">
                                    <option value="New York - JFK">🗽 New York - JFK</option>
                                    <option value="London - LHR">🇬🇧 London - LHR</option>
                                    <option value="Paris - CDG">🇫🇷 Paris - CDG</option>
                                    <option value="Tokyo - NRT">🇯🇵 Tokyo - NRT</option>
                                    <option value="Dubai - DXB">🇦🇪 Dubai - DXB</option>
                                </optgroup>
                                <optgroup label="Asian Cities">
                                    <option value="Singapore - SIN">🇸🇬 Singapore - SIN</option>
                                    <option value="Bangkok - BKK">🇹🇭 Bangkok - BKK</option>
                                    <option value="Mumbai - BOM">🇮🇳 Mumbai - BOM</option>
                                    <option value="Delhi - DEL">🇮🇳 Delhi - DEL</option>
                                    <option value="Jakarta - CGK">🇮🇩 Jakarta - CGK</option>
                                </optgroup>
                                <optgroup label="Other Cities">
                                    <option value="Sydney - SYD">🇦🇺 Sydney - SYD</option>
                                    <option value="Toronto - YYZ">🇨🇦 Toronto - YYZ</option>
                                    <option value="Los Angeles - LAX">🇺🇸 Los Angeles - LAX</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>
                    
                    <button type="button" class="swap-cities" data-section="1">
                        <i class="bi bi-arrow-left-right"></i>
                    </button>
                </div>
                
                <div class="trip-type-section">
                    <span class="trip-type-label">Trip Type</span>
                    <div class="trip-toggle">
                        <input type="radio" id="roundtrip" name="tripType" class="FDtrip-type" data-type="return" @if(isset($flight_search['trip_type']) && $flight_search['trip_type'] == 'round') checked @endif>
                        <label for="roundtrip" class="toggle-btn">Round Trip</label>
                        
                        <input type="radio" id="oneway" name="tripType" class="FDtrip-type" data-type="oneway" @if(session('flight_search.trip_type') == 'oneway') checked @elseif(!session('flight_search.trip_type')) checked @endif>
                        <label for="oneway" class="toggle-btn">One Way</label>
                    </div>
                </div>
            </div>

            <!-- Second Row: Dates, Passengers, Class, Search -->
            <div class="search-row-bottom">
                <div class="input-wrapper">
                    <label class="input-label">Departure</label>
                    <div class="date-input">
                        <i class="bi bi-calendar3 date-icon"></i>
                        <input type="text" class="date-field FDflatpickr-input FDdeparture-date" 
                               value="@if(isset($flight_search['departure_date']) && $flight_search['departure_date']) {{ $flight_search['departure_date'] }} @else {{date('Y-m-d',strtotime('+3 Days'))}} @endif" 
                               id="departuredate" placeholder="Select date" readonly>
                    </div>
                </div>
                
                <div class="input-wrapper return-input {{ empty($flight_search['return_date']) ? 'FDhidden' : '' }}">
                    <label class="input-label">Return</label>
                    <div class="date-input">
                        <i class="bi bi-calendar3 date-icon"></i>
                        <input type="text" class="date-field FDflatpickr-input FDreturn-date" 
                               id="returndate" 
                               value="@if(isset($flight_search['return_date']) && $flight_search['return_date']) {{ $flight_search['return_date'] }} @else {{date('Y-m-d',strtotime('+7 Days'))}} @endif" 
                               placeholder="Select date" readonly>
                    </div>
                </div>
                
                <div class="input-wrapper">
                    <label class="input-label">Travelers</label>
                    <div class="passenger-input FSpassenger-trigger">
                        <i class="bi bi-people passenger-icon"></i>
                        <span class="passenger-value FStotal-seat">{{ isset($flight_search['passenger_count']) && $flight_search['passenger_count'] ? $flight_search['passenger_count'] : 1 }} Passenger</span>
                    </div>
                    
                    <!-- Passenger Popup -->
                    <div class="popup-overlay FSpopup FSpopup-passenger">
                        <div class="popup-content">
                            <h3>Select Passengers</h3>
                            <div class="passenger-row">
                                <div class="passenger-info">
                                    <span class="passenger-type">Adults</span>
                                    <span class="passenger-desc">12+ years</span>
                                </div>
                                <div class="counter">
                                    <button class="counter-btn FSminus" data-type="adult">−</button>
                                    <span class="counter-value" data-type="adult" id="adult-count">{{ isset($flight_search['adult']) && $flight_search['adult'] ? $flight_search['adult'] : 1 }}</span>
                                    <button class="counter-btn FSplus" data-type="adult">+</button>
                                </div>
                            </div>
                            <div class="passenger-row">
                                <div class="passenger-info">
                                    <span class="passenger-type">Children</span>
                                    <span class="passenger-desc">2-11 years</span>
                                </div>
                                <div class="counter">
                                    <button class="counter-btn FSminus" data-type="child">−</button>
                                    <span class="counter-value" data-type="child" id="child-count">{{ isset($flight_search['children']) && $flight_search['children'] ? $flight_search['children'] : 0 }}</span>
                                    <button class="counter-btn FSplus" data-type="child">+</button>
                                </div>
                            </div>
                            <div class="passenger-row">
                                <div class="passenger-info">
                                    <span class="passenger-type">Infants</span>
                                    <span class="passenger-desc">Under 2 years</span>
                                </div>
                                <div class="counter">
                                    <button class="counter-btn FSminus" data-type="infant">−</button>
                                    <span class="counter-value" data-type="infant" id="infant-count">{{ isset($flight_search['infants']) && $flight_search['infants'] ? $flight_search['infants'] : 0 }}</span>
                                    <button class="counter-btn FSplus" data-type="infant">+</button>
                                </div>
                            </div>
                            <button class="apply-btn FSadd-btn">Apply</button>
                        </div>
                    </div>
                </div>
                
                <div class="input-wrapper">
                    <label class="input-label">Class</label>
                    <div class="class-input FSclass-trigger">
                        <i class="bi bi-star class-icon"></i>
                        <span class="class-value FSselected-class">{{ isset($flight_search['flight_type']) && $flight_search['flight_type'] ? ucfirst($flight_search['flight_type']) : 'Economy'}}</span>
                    </div>
                    
                    <!-- Class Popup -->
                    <div class="popup-overlay FSclass-popup">
                        <div class="popup-content">
                            <h3>Select Class</h3>
                            <div class="class-options">
                                <div class="class-option {{ (isset($flight_search['flight_type']) && $flight_search['flight_type'] == 'economy') ? 'selected' : '' }}" data-class="economy">
                                    <span class="class-name">Economy</span>
                                    <span class="class-desc">Standard seating</span>
                                </div>
                                <div class="class-option {{ (isset($flight_search['flight_type']) && $flight_search['flight_type'] == 'premium_economy') ? 'selected' : '' }}" data-class="premium_economy">
                                    <span class="class-name">Premium Economy</span>
                                    <span class="class-desc">Extra legroom</span>
                                </div>
                                <div class="class-option {{ (isset($flight_search['flight_type']) && $flight_search['flight_type'] == 'business') ? 'selected' : '' }}" data-class="business">
                                    <span class="class-name">Business</span>
                                    <span class="class-desc">Premium service</span>
                                </div>
                                <div class="class-option {{ (isset($flight_search['flight_type']) && $flight_search['flight_type'] == 'first') ? 'selected' : '' }}" data-class="first">
                                    <span class="class-name">First Class</span>
                                    <span class="class-desc">Luxury experience</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="search-button">
                    <i class="bi bi-search"></i>
                    <span>Search</span>
                </button>
            </div>
        </form>
    </div>
</div>



<style>
.sleek-flight-search {
    max-width: 900px;
    margin: 1.5rem auto;
    padding: 0;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.glass-container {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(20px);
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.search-header {
    text-align: center;
    margin-bottom: 1.5rem;
}

.search-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.75rem;
}

.nav-pills {
    display: inline-flex;
    background: #f9fafb;
    border-radius: 8px;
    padding: 3px;
    gap: 3px;
}

.pill {
    padding: 6px 16px;
    border-radius: 6px;
    text-decoration: none;
    color: #6b7280;
    font-weight: 500;
    font-size: 14px;
    transition: all 0.2s;
}

.pill.active {
    background: #3b82f6;
    color: white;
}

.trip-type-section {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
}

.trip-type-label {
    font-size: 11px;
    text-align:start;
    color: #6b7280;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.trip-toggle {
    display: flex;
    background: #f3f4f6;
    border-radius: 8px;
    padding: 4px;
    width: fit-content;
}

.trip-toggle input[type="radio"] {
    display: none;
}

.toggle-btn {
    padding: 8px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    font-size: 14px;
    color: #6b7280;
    transition: all 0.2s;
}

.trip-toggle input:checked + .toggle-btn {
    background: #3b82f6;
    color: white;
}

.search-row-top {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 1rem;
    align-items: end;
    margin-bottom: 1rem;
}

.from-to-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    position: relative;
}

.from-to-wrapper {
    position: relative;
}

.search-row-bottom {
    display: grid;
     grid-template-columns: repeat(auto-fit, minmax(0, 1fr));
    gap: 1rem;
    align-items: end;
}

.city-input, .date-input, .passenger-input, .class-input {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 6px 8px;
    background: white;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    transition: all 0.2s;
    cursor: pointer;
    min-height: 28px;
}

.city-icon, .date-icon, .passenger-icon, .class-icon {
    font-size: 12px;
    color: #6b7280;
}

.city-label, .date-label, .passenger-label, .class-label {
    font-size: 10px;
    color: #6b7280;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 1px;
}

.city-input:hover, .date-input:hover, .passenger-input:hover, .class-input:hover {
    border-color: #3b82f6;
}

.city-icon, .date-icon, .passenger-icon, .class-icon {
    font-size: 12px;
    color: #6b7280;
}

.city-label, .date-label, .passenger-label, .class-label {
    font-size: 10px;
    color: #6b7280;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 1px;
}

.input-wrapper {
    display: flex;
    flex-direction: column;
    gap: 4px;
    position: relative;
}

.input-label {
    font-size: 11px;
    color: #6b7280;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 2px;
}

.city-input, .date-input, .passenger-input, .class-input {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 6px 8px;
    background: white;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    transition: all 0.2s;
    cursor: pointer;
    min-height: 28px;
}

.city-icon, .date-icon, .passenger-icon, .class-icon {
    font-size: 12px;
    color: #6b7280;
}

.city-field, .date-field {
    border: none;
    background: transparent;
    font-size: 12px;
    font-weight: 500;
    color: #374151;
    outline: none;
    width: 100%;
}

/* Fix input heights to 48px */
.city-input, .date-input, .passenger-input, .class-input {
    height: 48px !important;
    min-height: 48px !important;
    box-sizing: border-box;
}

.search-button {
    height: 48px !important;
    padding: 0 16px;
}

/* Select2 Dropdown Styling */
.select2-container {
    width: 100% !important;
    z-index: 9999;
}

.select2-dropdown {
    z-index: 9999 !important;
    border: 1px solid #d1d5db !important;
    border-radius: 8px !important;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
    margin-top: 4px !important;
}

.select2-container--default .select2-results__options {
    max-height: 200px !important;
}

.select2-container--default .select2-results__option {
    padding: 8px 10px !important;
    font-size: 12px !important;
    border-bottom: 1px solid #f3f4f6 !important;
    transition: all 0.2s !important;
}

.select2-container--default .select2-results__option:last-child {
    border-bottom: none !important;
}

.select2-container--default .select2-results__option--highlighted {
    background-color: #eff6ff !important;
    color: #1f2937 !important;
}

.select2-container--default .select2-results__option[aria-selected=true] {
    background-color: #3b82f6 !important;
    color: white !important;
}

.select2-container--default .select2-selection--single {
    border: none !important;
    background: transparent !important;
    height: auto !important;
    padding: 0 !important;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #374151 !important;
    font-size: 12px !important;
    font-weight: 500 !important;
    padding: 0 !important;
    line-height: normal !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    display: none !important;
}

.route-card, .date-card, .passenger-card {
    overflow: visible !important;
}

.city-input {
    overflow: visible !important;
    position: relative;
    z-index: 1
}

/* Popular Routes Styling */
.select2-results__group {
    background: #f8fafc !important;
    color: #6b7280 !important;
    font-size: 11px !important;
    font-weight: 600 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    padding: 8px 12px 4px !important;
    border-bottom: 1px solid #e5e7eb !important;
}

.passenger-value, .class-value {
    font-size: 12px;
    font-weight: 500;
    color: #374151;
}

.swap-cities {
    position: absolute;
    top: calc(100% - 40px);
    left: 50%;
    transform: translateX(-50%);
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: 2px solid #d1d5db;
    background: white;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.swap-cities svg {
    width: 14px;
    height: 14px;
}

.swap-cities:hover {
    border-color: #3b82f6;
    color: #3b82f6;
    transform: translateX(-50%) rotate(180deg);
}

.date-card {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
    padding: 0;
    background: transparent;
    border: none;
}

.passenger-card {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
    padding: 0;
    background: transparent;
    border: none;
}

.search-button {
    width: 100%;
    padding: 12px 24px;
    background: #3b82f6;
    border: none;
    border-radius: 8px;
    color: white;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

.search-button:hover {
    background: #2563eb;
}

.search-button:active {
    background: #1d4ed8;
}

.FDhidden {
    display: none !important;
}

/* Popup Styles */
.popup-overlay {
    position: absolute;
    top: 100%;
    left: 0;
    width: 100%;
    background: transparent;
    display: none;
    z-index: 1000;
    margin-top: 4px;
    min-width:350px;
    padding:.3rem;
}

.popup-content {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    max-width: 350px;
    width: 100%;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.popup-content h3 {
    margin: 0 0 1.5rem 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #1e293b;
}

.passenger-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid #f1f5f9;
}

.passenger-row:last-of-type {
    border-bottom: none;
}

.passenger-type {
    font-weight: 600;
    color: #1e293b;
    display: block;
}

.passenger-desc {
    font-size: 14px;
    color: #64748b;
}

.counter {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.counter-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 2px solid #e2e8f0;
    background: white;
    color: #64748b;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    transition: all 0.2s;
}

.counter-btn:hover {
    border-color: #3b82f6;
    color: #3b82f6;
}

.counter-value {
    font-weight: 600;
    min-width: 20px;
    text-align: center;
}

.apply-btn {
    width: 100%;
    padding: 10px;
    background: #3b82f6;
    border: none;
    border-radius: 6px;
    color: white;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    margin-top: 1rem;
    transition: all 0.2s;
}

.apply-btn:hover {
    background: #2563eb;
}

.class-options {
    display: grid;
    gap: 8px;
}

.class-option {
    padding: 0.75rem;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
    cursor: pointer;
    transition: all 0.2s;
}

.class-option:hover {
    border-color: #e2e8f0;
}

.class-option.selected {
    border-color: #3b82f6;
    background: #eff6ff;
}

.class-name {
    font-weight: 600;
    color: #1e293b;
    display: block;
}

.class-desc {
    font-size: 14px;
    color: #64748b;
}

/* Modern Flatpickr Calendar Styling */
.flatpickr-calendar {
    background: #ffffff;
    border: none;
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12);
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    overflow: hidden;
}

.flatpickr-months {
    background: #3b82f6;
    border-radius: 16px 16px 0 0;
    padding: 16px;
    position: relative;
}

.flatpickr-months::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, transparent 100%);
    pointer-events: none;
}

.flatpickr-month {
    color: white;
    font-weight: 700;
    font-size: 16px;
}

.flatpickr-current-month {
    color: white;
    font-size: 16px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.flatpickr-monthDropdown-months {
    background: transparent;
    border: none;
    color: white;
    font-size: 16px;
    font-weight: 700;
    outline: none;
    cursor: pointer;
}

.numInputWrapper {
    display: flex;
    align-items: center;
}

.numInput.cur-year {
    background: transparent;
    border: none;
    color: white;
    font-size: 16px;
    font-weight: 700;
    width: 60px;
    text-align: center;
    outline: none;
}

.arrowUp, .arrowDown {
    display: none;
}
.flatpickr-next-month{
    right:20px !important;
}
.flatpickr-prev-month{
   left:20px !important; 
}
.flatpickr-prev-month, .flatpickr-next-month {
    color: rgba(255, 255, 255, 0.8);
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    top:50% !important;
    transform: translateY(-50%) !important;
}

.flatpickr-prev-month:hover, .flatpickr-next-month:hover {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    transform: scale(1.1);
}

.flatpickr-weekdays {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 8px 0;
}

.flatpickr-weekday {
    color: #64748b;
    font-weight: 600;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.flatpickr-days {
    padding: 12px;
}

.flatpickr-day {
    border-radius: 6px;
    margin: 1px;
    transition: all 0.3s ease;
    font-weight: 500;
    width: 28px;
    height: 28px;
    line-height: 28px;
    border: none;
    position: relative;
    overflow: hidden;
    font-size: 11px;
}

.flatpickr-day::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.1), transparent);
    transition: left 0.5s;
}

.flatpickr-day:hover::before {
    left: 100%;
}

.flatpickr-day:hover {
    background: #eff6ff;
    border-color: #3b82f6;
    color: #1e40af;
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);
}

.flatpickr-day.selected {
    background: #3b82f6;
    border-color: #3b82f6;
    color: white;
    font-weight: 600;
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.flatpickr-day.today {
    border: 2px solid #3b82f6;
    color: #3b82f6;
    font-weight: 600;
    background: rgba(59, 130, 246, 0.1);
}

.flatpickr-day.today:hover {
    background: #3b82f6;
    color: white;
}

.flatpickr-day.inRange {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
    border-radius: 0;
}

.flatpickr-day.startRange, .flatpickr-day.endRange {
    background: #3b82f6;
    color: white;
    border-radius: 8px;
}

.flatpickr-day.nextMonthDay, .flatpickr-day.prevMonthDay {
    color: #cbd5e1;
}

.flatpickr-day.disabled {
    color: #e2e8f0;
    cursor: not-allowed;
}

.flatpickr-day.disabled:hover {
    background: transparent;
    transform: none;
    box-shadow: none;
}
.select2-selection__placeholder.select2-selection__placeholder{
    color:#374151 !important
}
@media (max-width: 768px) {
    .sleek-flight-search {
        margin: 0.5rem;
    }
    
    .glass-container {
        margin: 0;
        padding: 1rem;
        padding-top:60px;
    }
    .trip-type-section{
        position:absolute;
        top:16px;
        right:16px;
    }
    .trip-type-label{
        display:none
    }
    .search-row-top {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .from-to-container {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .search-row-bottom {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .swap-cities {
        top:50%;
        transform: translateX(-50%) translateY(-50%);
        transform-origin: center;
        
    }
    
    .swap-cities:hover {
        transform: translateX(-50%) translateY(-50%) scale(1.1) rotate(180deg);
    }
    @media (max-width: 768px) {
    .swap-cities {
        top: calc(100% - 23px);
        width:32px;
        height:32px;
        left:unset;
        right: -7px;
        transform: translateX(-50%) translateY(-50%);
        transform-origin: center;
        z-index: 1;
    }
}
    
    .trip-toggle {
        justify-self: center;
    }
    
    .popup-overlay {
        min-width: 280px;
        left: 50%;
        transform: translateX(-50%);
    }
    
    .popup-content {
        max-width: 280px;
        padding: 1rem;
    }
    
    .select2-dropdown {
        z-index: 10000 !important;
    }
}
</style>

<script>
    window.adult = {{ isset($flight_search['adults']) && $flight_search['adults'] ? $flight_search['adults'] : 1 }};
    window.child = {{ isset($flight_search['children']) && $flight_search['children'] ? $flight_search['children'] : 0 }};
    window.infant = {{ isset($flight_search['infants']) && $flight_search['infants'] ? $flight_search['infants'] : 0 }};

    // Trip type toggle functionality
    document.querySelectorAll('.FDtrip-type').forEach(radio => {
        radio.addEventListener('change', function() {
            const returnInput = document.querySelector('.return-input');
            if (this.getAttribute('data-type') === 'return') {
                returnInput.classList.remove('FDhidden');
            } else {
                returnInput.classList.add('FDhidden');
            }
        });
    });

    // Passenger popup functionality
    document.querySelector('.FSpassenger-trigger').addEventListener('click', function() {
        document.querySelector('.FSpopup-passenger').style.display = 'flex';
    });

    // Class popup functionality
    document.querySelector('.FSclass-trigger').addEventListener('click', function() {
        document.querySelector('.FSclass-popup').style.display = 'flex';
    });

    // Close popups when clicking outside
    document.querySelectorAll('.popup-overlay').forEach(popup => {
        popup.addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
    });

    // Counter functionality
    document.querySelectorAll('.FSplus, .FSminus').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const type = this.getAttribute('data-type');
            const counter = document.getElementById(type + '-count');
            let count = parseInt(counter.textContent);
            
            if (this.classList.contains('FSplus')) {
                count++;
            } else if (this.classList.contains('FSminus') && count > 0) {
                if (type === 'adult' && count === 1) return;
                count--;
            }
            
            counter.textContent = count;
        });
    });

    // Apply passenger selection
    document.querySelector('.FSadd-btn').addEventListener('click', function() {
        const adults = parseInt(document.getElementById('adult-count').textContent);
        const children = parseInt(document.getElementById('child-count').textContent);
        const infants = parseInt(document.getElementById('infant-count').textContent);
        const total = adults + children + infants;
        
        document.querySelector('.FStotal-seat').textContent = total + ' Passenger' + (total > 1 ? 's' : '');
        document.querySelector('.FSpopup-passenger').style.display = 'none';
    });

    // Class selection
    document.querySelectorAll('.class-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.class-option').forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            
            const className = this.querySelector('.class-name').textContent;
            document.querySelector('.FSselected-class').textContent = className;
            document.querySelector('.FSclass-popup').style.display = 'none';
        });
    });

    // Swap cities functionality
    document.querySelector('.swap-cities').addEventListener('click', function() {
        const origin = document.getElementById('origin');
        const destination = document.getElementById('destination');
        
        const tempValue = origin.value;
        origin.value = destination.value;
        destination.value = tempValue;
    });

    // Initialize Select2 if available
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('.city-field').select2({
            dropdownParent: $('.sleek-flight-search'),
            minimumResultsForSearch: Infinity,
            width: '100%'
        });
    }

    // Form submission
    document.getElementById('flightssearch').addEventListener('submit', function(event) {
        event.preventDefault();

        const origin = document.getElementById('origin').value.trim();
        const destination = document.getElementById('destination').value.trim();
        const departure_date = document.getElementById('departuredate').value.trim();
        const return_date = document.getElementById('returndate').value.trim();
        const get_trip = document.querySelector('.FDtrip-type:checked');
        const trip = get_trip.getAttribute('data-type');
        const adults = document.getElementById("adult-count").textContent;
        const childs = document.getElementById("child-count").textContent;
        const infant = document.getElementById("infant-count").textContent;
        const class_active = document.querySelector(".FSselected-class");
        const class_type = class_active?.textContent.trim().toLowerCase();

        if (!origin || !destination || !departure_date) {
            alert('Please fill in all required fields.');
            return;
        }

        const baseUrl = "{{ url('flights_search') }}";

        if (trip == 'oneway') {
            var custom_url = `${baseUrl}/${origin}/${destination}/${trip}/${class_type}/${departure_date}/${adults}/${childs}/${infant}`;
        }

        if (trip == 'return') {
            if (!return_date) {
                alert('Please select return date for round trip.');
                return;
            }
            var custom_url = `${baseUrl}/${origin}/${destination}/round/${class_type}/${departure_date}/${return_date}/${adults}/${childs}/${infant}`;
        }

        window.location.href = custom_url;
    });
</script>