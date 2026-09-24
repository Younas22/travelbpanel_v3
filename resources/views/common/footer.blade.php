<style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap');

        * {
            font-family: 'Poppins', sans-serif;
        }

        .travel-bg {
            background-image:
                linear-gradient(135deg, rgba(0, 119, 190, 0.95) 0%, rgba(0, 92, 143, 0.92) 100%),
                url('https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=1200');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }


        .glass-effect {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .hover-lift {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .hover-lift:hover {
            transform: translateY(-5px);
        }

        .wave {
            position: absolute;
            top: -2px;
            left: 0;
            width: 100%;
            overflow: hidden;
            line-height: 0;
        }

        .wave svg {
            position: relative;
            display: block;
            width: calc(100% + 1.3px);
            height: 80px;
        }

        .wave .shape-fill {
            fill: #EFF4F9;
        }

        .footer-link {
            position: relative;
            overflow: hidden;
        }

        .footer-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: #60a5fa;
            transition: width 0.3s ease;
        }

        .footer-link:hover::after {
            width: 100%;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .float-animation {
            animation: float 3s ease-in-out infinite;
        }

        .social-icon {
            position: relative;
            overflow: hidden;
        }

        .social-icon::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .social-icon:hover::before {
            width: 200px;
            height: 200px;
        }

        /* Logo Container Fix */
        .logo-container {
            background: white;
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 200px;
            width: auto;
        }

        .logo-container img {
            max-width: 100%;
            max-height: 60px;
            width: auto;
            height: auto;
            object-fit: contain;
        }

        .logo-fallback {
            display: none;
        }
    </style>




    <!-- UNIQUE FOOTER WITH TRAVEL BACKGROUND -->
    <footer class="relative travel-bg text-white overflow-hidden">

        <!-- Decorative Wave -->
        <div class="wave">
            <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" class="shape-fill"></path>
            </svg>
        </div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-24 pb-8">

            <!-- Main Footer Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-8 mb-12">

                <!-- Brand Section - Takes more space -->
                <div class="lg:col-span-5">
                    <!-- Logo with Fallback -->

                    <div class="flex items-center mb-6 float-animation">
                        <img
                            src="{{ getSettingImage('business_logo_white', 'branding') }}"
                            alt="Logo"
                            class="w-32 h-32 object-contain"
                            style="max-height: 60px; width: auto; height: auto;"
                        >
                    </div>


                    <p class="text-blue-100 text-sm leading-relaxed mb-6">
                        <?= getSetting('meta_description', 'seo') ?>
                    </p>

                    <!-- Contact Cards -->
                    <div class="space-y-3 mb-6">
                        <div class="glass-effect rounded-lg p-3 flex items-center gap-3 hover-lift">
                            <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-envelope text-white"></i>
                            </div>
                            <div>
                                <div class="text-xs text-blue-200">{{t('footer.email_us')}}</div>
                                <div class="text-sm font-semibold"><?=getSetting('contact_email', 'contact')?></div>
                            </div>
                        </div>

                        <div class="glass-effect rounded-lg p-3 flex items-center gap-3 hover-lift">
                            <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fab fa-whatsapp text-white"></i>
                            </div>
                            <div>
                            <div class="text-xs text-blue-200">{{ t('footer.whatsapp') }}</div>

                            @php
                            $phone = getSetting('contact_phone', 'contact');
                            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
                            $whatsappLink = 'https://wa.me/' . $cleanPhone;
                            @endphp

                            <div class="text-sm font-semibold">
                            <a href="{{ $whatsappLink }}" target="_blank">
                            {{ $phone }}
                            </a>
                            </div>
                            </div>
                        </div>
                    </div>

                    @php
                        $socialLinks = getSetting('all', 'social');
                    @endphp

                    <div class="flex gap-3">
                        @if(!empty($socialLinks['facebook_url']))
                            <a href="{{ $socialLinks['facebook_url'] }}" target="_blank" class="social-icon w-11 h-11 bg-white/10 rounded-xl flex items-center justify-center hover:bg-blue-500 transition-colors relative z-10">
                                <i class="fab fa-facebook-f text-white relative z-10"></i>
                            </a>
                        @endif

                        @if(!empty($socialLinks['twitter_url']))
                            <a href="{{ $socialLinks['twitter_url'] }}" target="_blank" class="social-icon w-11 h-11 bg-white/10 rounded-xl flex items-center justify-center hover:bg-sky-500 transition-colors relative z-10">
                                <i class="fab fa-twitter text-white relative z-10"></i>
                            </a>
                        @endif

                        @if(!empty($socialLinks['instagram_url']))
                            <a href="{{ $socialLinks['instagram_url'] }}" target="_blank" class="social-icon w-11 h-11 bg-white/10 rounded-xl flex items-center justify-center hover:bg-pink-500 transition-colors relative z-10">
                                <i class="fab fa-instagram text-white relative z-10"></i>
                            </a>
                        @endif

                        @if(!empty($socialLinks['linkedin_url']))
                            <a href="{{ $socialLinks['linkedin_url'] }}" target="_blank" class="social-icon w-11 h-11 bg-white/10 rounded-xl flex items-center justify-center hover:bg-blue-700 transition-colors relative z-10">
                                <i class="fab fa-linkedin-in text-white relative z-10"></i>
                            </a>
                        @endif

                        @if(!empty($socialLinks['youtube_url']))
                            <a href="{{ $socialLinks['youtube_url'] }}" target="_blank" class="social-icon w-11 h-11 bg-white/10 rounded-xl flex items-center justify-center hover:bg-red-600 transition-colors relative z-10">
                                <i class="fab fa-youtube text-white relative z-10"></i>
                            </a>
                        @endif

                        @if(!empty($socialLinks['whatsapp_number']))
                            <a href="https://wa.me/{{ preg_replace('/\D/', '', $socialLinks['whatsapp_number']) }}" target="_blank" class="social-icon w-11 h-11 bg-white/10 rounded-xl flex items-center justify-center hover:bg-green-500 transition-colors relative z-10">
                                <i class="fab fa-whatsapp text-white relative z-10"></i>
                            </a>
                        @endif
                    </div>


                </div>

                <!-- Quick Links -->
                <div class="lg:col-span-2">
                    <h4 class="text-lg font-bold mb-4 flex items-center gap-2">
                        <span class="w-1 h-6 bg-blue-400 rounded"></span>
                        {{t('footer.quick_links')}}
                    </h4>
                    <ul class="space-y-2">
                        @foreach (get_menu_items('footer_quick_links') as $item)
                        <li><a href="{{ $item->full_url }}" class="footer-link text-blue-100 hover:text-white text-sm inline-block pb-1">{{t('footer.'.$item->name)}}</a></li>
                        @endforeach
                    </ul>
                </div>

                <!-- Our Services — driven by active modules -->
                <div class="lg:col-span-2">
                    <h4 class="text-lg font-bold mb-4 flex items-center gap-2">
                        <span class="w-1 h-6 bg-blue-400 rounded"></span>
                        {{t('footer.our_services')}}
                    </h4>
                    @php
                        $footerModUrlMap = ['hotel'=>'/hotels','flight'=>'/flights','visa'=>'/visa','tours'=>'/tours','umrah'=>'/umrah'];
                    @endphp
                    <ul class="space-y-2">
                        @foreach (\App\Models\Module::active()->orderBy('sort_order')->get() as $fMod)
                            @php $fUrl = url($footerModUrlMap[$fMod->slug] ?? '/'.$fMod->slug); @endphp
                            @if($fMod->slug === 'visa')
                                <li><a href="{{ route('visa.create') }}" class="footer-link text-blue-100 hover:text-white text-sm inline-block pb-1">{{ t('footer.'.$fMod->name) }}</a></li>
                            @else
                                <li><a href="{{ $fUrl }}" class="footer-link text-blue-100 hover:text-white text-sm inline-block pb-1">{{ t('footer.'.$fMod->name) }}</a></li>
                            @endif
                        @endforeach
                    </ul>
                </div>

                <!-- Newsletter - Compact Design -->
                <div class="lg:col-span-3">
                    <h4 class="text-lg font-bold mb-4 flex items-center gap-2">
                        <span class="w-1 h-6 bg-blue-400 rounded"></span>{{t('footer.newsletter')}}
                    </h4>
                    <p class="text-blue-100 text-xs mb-4">{{t('footer.newsletter_desc')}}</p>
                    <form id="newsletterForm" class="space-y-2">
                        @csrf
                        <input
                            type="email"
                            id="newsletterEmail"
                            name="email"
                            placeholder="{{t('footer.your_email')}}"
                            required
                            class="w-full px-4 py-2.5 rounded-lg bg-white/10 border border-white/20 text-white placeholder-blue-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:bg-white/15 transition-all"
                        >
                        <button type="submit" id="newsletterBtn" class="w-full bg-white text-blue-600 font-bold py-2.5 rounded-lg hover:bg-blue-50 transition-all hover:shadow-xl flex items-center justify-center gap-2 text-sm">
                            <i class="fas fa-paper-plane"></i>
                            <span id="newsletterBtnText">{{t('footer.subscribe_now')}}</span>
                        </button>
                    </form>
                </div>

            </div>

            <!-- Footer Bottom -->
            <div class="border-t border-white/20 pt-6">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="flex items-center gap-3 text-sm text-blue-100">
                        <span>© <?= date('Y'); ?> <strong class="text-white"><?= getSetting('business_name', 'main') ?></strong></span>
                        <span class="hidden md:inline">•</span>
                        <span class="hidden md:inline">{{t('footer.made_by')}} <i class="fas fa-heart text-red-400"></i> <a href="https://travelbookingpanel.com/">TravelBookingPanel</a></span>
                    </div>
                    <div class="flex gap-6 text-sm">
                         @foreach (get_menu_items('footer_support') as $item)
                        <a href="{{ $item->full_url }}" class="text-blue-100 hover:text-white transition-colors">{{t('footer.'.$item->name)}}</a>
                        @endforeach

                    </div>
                </div>
            </div>

        </div>

        <!-- Decorative Elements -->
        <div class="absolute top-20 right-10 opacity-10">
            <i class="fas fa-plane text-white text-9xl transform rotate-45"></i>
        </div>
        <div class="absolute bottom-20 left-10 opacity-10">
            <i class="fas fa-globe text-white text-8xl"></i>
        </div>


        @php
            $tour_search = session('tour_search');
            if (($flight_search['trip_type'] ?? '') === 'round') {
                $trip = "round";
            } elseif (($flight_search['trip_type'] ?? '') === 'oneway') {
                $trip = "oneway";
            } else {
                $trip = "oneway";
            }
        @endphp
    </footer>


    <script src="{{ url('public/assets/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ url('public/assets/libs/select2/js/select2.min.js') }}"></script>
    <script src="{{ url('public/assets/libs/flatpickr/flatpickr.min.js') }}"></script>
    <script>
        function toggleMobileMenu() {
            const hamburger = document.querySelector('.hamburger');
            const mobileMenu = document.querySelector('.mobile-menu');
            hamburger.classList.toggle('active');
            mobileMenu.classList.toggle('active');
        }
    </script>



<script>
    // ========== API Configuration ==========
    const API_BASE_URL = "{{ url('/') }}";

    // ========== GLOBAL VARIABLES ==========
    let tripType = "<?=$trip?>";
    let hotelTravelers = {
        adult: <?= isset($hotel_search['adults']) && $hotel_search['adults'] ? (int) $hotel_search['adults'] : 2 ?>,
        child: <?= isset($hotel_search['childs']) && $hotel_search['childs'] ? (int) $hotel_search['childs'] : 0 ?>,
        room: <?= isset($hotel_search['rooms']) && $hotel_search['rooms'] ? (int) $hotel_search['rooms'] : 1 ?>
    };
    // Ages the admin already picked for this session's search, so the Modify
    // Search form can pre-select them instead of showing blank dropdowns.
    // hotel_search.child_age is stored as an array (child_age[0] = child 1's
    // age, etc.) — the isset/is_string branch here is just a defensive
    // fallback in case an older string-formatted session value is still around.
    let hotelChildAgesFromSession = <?php
        $sessionAges = $hotel_search['child_age'] ?? [];
        if (is_string($sessionAges)) {
            $sessionAges = array_filter(array_map('trim', explode(',', $sessionAges)));
        }
        echo json_encode(array_values(array_map('strval', (array) $sessionAges)));
    ?>;
    let hotelDestinationFromSession = "<?= isset($hotel_search['city']) ? addslashes($hotel_search['city']) : '' ?>";
    let hotelCheckinFromSession = "<?= isset($hotel_search['checkin']) && $hotel_search['checkin'] ? date('d-m-Y', strtotime($hotel_search['checkin'])) : '' ?>";
    let hotelCheckoutFromSession = "<?= isset($hotel_search['checkout']) && $hotel_search['checkout'] ? date('d-m-Y', strtotime($hotel_search['checkout'])) : '' ?>";
    let hotelNationalityFromSession = "<?= isset($hotel_search['nationality']) ? addslashes($hotel_search['nationality']) : '' ?>";
    let flightPassengers = { adult: "<?=isset($flight_search['adult']) && $flight_search['adult'] ? $flight_search['adult'] : 1?>", child: <?=isset($flight_search['children']) && $flight_search['children'] ? $flight_search['children'] : 0?>, infant: <?=isset($flight_search['infants']) && $flight_search['infants'] ? $flight_search['infants'] : 0?> };
    let toursTravelers = { adult: {{ isset($tour_search['adult']) ? $tour_search['adult'] : 2 }}, child: {{ isset($tour_search['child']) ? $tour_search['child'] : 0 }} };

    // ========== DATE FORMATTERS ==========
    function formatDate(date) {
        const d = new Date(date);
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        return `${day}-${month}-${year}`;
    }

    // ========== DROPDOWN MANAGEMENT ==========
    function closeAllHotelDropdowns() {
        const hotelDestinationDropdown = document.getElementById('hotelDestinationDropdown');
        const hotelTravelerDropdown = document.getElementById('hotelTravelerDropdown');
        const hotelCountryDropdown = document.getElementById('hotelCountryDropdown');
        const dropdownOverlay = document.getElementById('dropdownOverlay');

        if (hotelDestinationDropdown) hotelDestinationDropdown.classList.remove('active');
        if (hotelTravelerDropdown) hotelTravelerDropdown.classList.remove('active');
        if (hotelCountryDropdown) hotelCountryDropdown.classList.remove('active');
        if (dropdownOverlay) dropdownOverlay.classList.remove('active');
    }

    function closeAllFlightDropdowns() {
        const flightFromDropdown = document.getElementById('flightFromDropdown');
        const flightToDropdown = document.getElementById('flightToDropdown');
        const flightPassengerDropdown = document.getElementById('flightPassengerDropdown');
        const flightClassDropdown = document.getElementById('flightClassDropdown');
        const flightDropdownOverlay = document.getElementById('flightDropdownOverlay');

        if (flightFromDropdown) flightFromDropdown.classList.remove('active');
        if (flightToDropdown) flightToDropdown.classList.remove('active');
        if (flightPassengerDropdown) flightPassengerDropdown.classList.remove('active');
        if (flightClassDropdown) flightClassDropdown.classList.remove('active');
        if (flightDropdownOverlay) flightDropdownOverlay.classList.remove('active');
    }

    function closeAllUmrahDropdowns() {
        const umrahFromDropdown = document.getElementById('umrahFromDropdown');
        const umrahToDropdown = document.getElementById('umrahToDropdown');
        const umrahPassengerDropdown = document.getElementById('umrahPassengerDropdown');
        const umrahDropdownOverlay = document.getElementById('umrahDropdownOverlay');

        if (umrahFromDropdown) umrahFromDropdown.classList.remove('active');
        if (umrahToDropdown) umrahToDropdown.classList.remove('active');
        if (umrahPassengerDropdown) umrahPassengerDropdown.classList.remove('active');
        if (umrahDropdownOverlay) umrahDropdownOverlay.classList.remove('active');
    }

    function showOverlay(overlayId) {
        if (window.innerWidth <= 1024) {
            const overlay = document.getElementById(overlayId);
            if (overlay) overlay.classList.add('active');
        }
    }

    // ========== API FUNCTIONS ==========
    async function loadHotelDestinations(searchTerm, listElement) {
        if (searchTerm.length < 3) {
            listElement.innerHTML = '<div class="loading">Type at least 3 characters...</div>';
            return;
        }

        listElement.innerHTML = '<div class="loading">Searching...</div>';

        try {
            const response = await fetch(`${API_BASE_URL}/api/hotel_destinations?search=${searchTerm}`);
            const data = await response.json();

            if (data.success && data.data && data.data.length > 0) {
                listElement.innerHTML = '';
                data.data.forEach(destination => {
                    const destItem = document.createElement('div');
                    destItem.className = 'destination-item';
                    destItem.dataset.name = destination.city;
                    destItem.dataset.country = destination.country;
                    destItem.innerHTML = `
                        <i class="fas fa-map-marker-alt destination-icon"></i>
                        <div class="destination-details">
                            <div class="destination-name">${destination.city}</div>
                            <div class="destination-location">${destination.country}</div>
                        </div>
                    `;
                    listElement.appendChild(destItem);
                });
            } else {
                listElement.innerHTML = '<div class="loading">No destinations found</div>';
            }
        } catch (error) {
            console.error('Error loading destinations:', error);
            listElement.innerHTML = '<div class="loading">Error loading destinations</div>';
        }
    }

    async function loadAirports(searchTerm, listElement) {
        if (searchTerm.length < 2) {
            listElement.innerHTML = '<div class="loading">Type at least 2 characters...</div>';
            return;
        }

        listElement.innerHTML = '<div class="loading">Searching...</div>';

        try {
            const response = await fetch(`${API_BASE_URL}/api/flights_airports?code=${searchTerm}`);
            const data = await response.json();

            if (data.success && data.data && data.data.length > 0) {
                listElement.innerHTML = '';
                data.data.forEach(airport => {
                    const airportItem = document.createElement('div');
                    airportItem.className = 'airport-item';
                    airportItem.dataset.name = airport.city || airport.airport;
                    airportItem.dataset.code = airport.code;
                    airportItem.innerHTML = `
                        <i class="fas fa-plane airport-icon"></i>
                        <div class="airport-details">
                            <div class="airport-name">${airport.city || airport.airport}</div>
                            <div class="airport-code">${airport.code} - ${airport.airport}</div>
                        </div>
                    `;
                    listElement.appendChild(airportItem);
                });
            } else {
                listElement.innerHTML = '<div class="loading">No airports found</div>';
            }
        } catch (error) {
            console.error('Error loading airports:', error);
            listElement.innerHTML = '<div class="loading">Error loading airports</div>';
        }
    }

    async function loadCountries(listElement, searchInput) {
        try {
            const response = await fetch(`${API_BASE_URL}/api/countries`);
            const data = await response.json();

            if (data.success && data.data && data.data.length > 0) {
                listElement.innerHTML = '';
                data.data.forEach(country => {
                    const countryItem = document.createElement('div');
                    countryItem.className = 'country-item';
                    countryItem.dataset.name = country.country;
                    countryItem.dataset.flag = country.flag;
                    countryItem.dataset.code = country.country_code;
                    countryItem.innerHTML = `
                        <span class="country-flag">${country.flag}</span>
                        <span class="country-name">${country.country}</span>
                    `;
                    listElement.appendChild(countryItem);
                });

                searchInput.addEventListener('input', (e) => {
                    const searchText = e.target.value.toLowerCase();
                    listElement.querySelectorAll('.country-item').forEach(item => {
                        const text = item.textContent.toLowerCase();
                        item.style.display = text.includes(searchText) ? 'flex' : 'none';
                    });
                });
            }
        } catch (error) {
            console.error('Error loading countries:', error);
            listElement.innerHTML = '<div class="loading">Error loading countries</div>';
        }
    }

    // ========== TAB SWITCHING ==========
    function initializeTabs() {
        const tabButtons = document.querySelectorAll('.tab-btn');

        tabButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                const tabName = button.dataset.tab;

                // If no data-tab (e.g. Visa link), let default behavior happen
                if (!tabName) return;

                // Prevent default and stop propagation
                e.preventDefault();
                e.stopPropagation();

                console.log('Switching to tab:', tabName);

                // Remove active from all buttons that have data-tab
                tabButtons.forEach(btn => {
                    if (btn.dataset.tab) {
                        btn.classList.remove('active');
                    }
                });

                // Hide all content tabs
                const allTabContents = document.querySelectorAll('.tab-content');
                allTabContents.forEach(content => {
                    content.classList.remove('active');
                });

                // Add active to clicked button
                button.classList.add('active');

                // Show the selected tab content
                const formElement = document.getElementById('form-' + tabName);
                if (formElement) {
                    formElement.classList.add('active');
                    console.log('Tab activated:', tabName);
                } else {
                    console.error('Form element not found for tab:', tabName);
                }
            });
        });
    }

    // ========== HOTEL CHILD AGE SELECTORS ==========
    // Renders one required age dropdown (2-11 years) per selected child,
    // directly below the child stepper, and removes them when the count drops.
    function renderHotelChildAges() {
        const container = document.getElementById('hotelChildAgesContainer');
        if (!container) return;

        const count = hotelTravelers.child || 0;

        // Preserve any ages already chosen while adjusting the count.
        const existingAges = Array.from(container.querySelectorAll('select[data-child-age-index]'))
            .map(select => select.value);

        if (count <= 0) {
            container.style.display = 'none';
            container.innerHTML = '';
            hideChildAgeError();
            return;
        }

        let html = '';
        for (let i = 1; i <= count; i++) {
            const previousValue = existingAges[i - 1] || hotelChildAgesFromSession[i - 1] || '';
            let options = '<option value="">{{ t("hotel.selectChildAge") }}</option>';
            for (let age = 2; age <= 11; age++) {
                options += `<option value="${age}" ${String(age) === previousValue ? 'selected' : ''}>${age} {{ t("hotel.years") }}</option>`;
            }
            html += `
                <div class="flex items-center justify-between gap-2">
                    <label class="text-[12px] text-gray-600 font-medium" for="hotelChildAge${i}">{{ t('hotel.child') }} ${i} {{ t('hotel.age') }}</label>
                    <select id="hotelChildAge${i}" data-child-age-index="${i}" required
                            class="text-[13px] border border-gray-300 rounded-lg px-2 py-1.5 bg-white focus:outline-none focus:ring-2 focus:ring-[#0077BE]">
                        ${options}
                    </select>
                </div>`;
        }
        container.innerHTML = html;
        container.style.display = 'block';

        // Clear the "please select an age" error as soon as the admin picks one.
        container.querySelectorAll('select[data-child-age-index]').forEach(select => {
            select.addEventListener('change', () => {
                if (validateHotelChildAges(false)) hideChildAgeError();
            });
        });
    }

    function hideChildAgeError() {
        const errorEl = document.getElementById('hotelChildAgeError');
        if (errorEl) errorEl.style.display = 'none';
    }

    // Returns true when every selected child has an age chosen. When
    // `showError` is true (default), reveals the inline message and focuses
    // the first empty dropdown so the admin knows exactly what to fix.
    function validateHotelChildAges(showError = true) {
        if ((hotelTravelers.child || 0) <= 0) return true;

        const ageSelects = Array.from(document.querySelectorAll('#hotelChildAgesContainer select[data-child-age-index]'));
        const firstMissing = ageSelects.find(select => !select.value);

        if (firstMissing || ageSelects.length < hotelTravelers.child) {
            if (showError) {
                const errorEl = document.getElementById('hotelChildAgeError');
                if (errorEl) errorEl.style.display = 'block';
                firstMissing?.focus();
            }
            return false;
        }

        return true;
    }

    // ========== HOTEL FORM INITIALIZATION ==========
    function initializeHotelForm() {
        // Hotel Destination
        const hotelDestinationBtn = document.getElementById('hotelDestinationBtn');
        const hotelDestinationDropdown = document.getElementById('hotelDestinationDropdown');
        const hotelDestinationDisplay = document.getElementById('hotelDestinationDisplay');
        const hotelDestinationValue = document.getElementById('hotelDestinationValue');
        const hotelDestinationSearch = document.getElementById('hotelDestinationSearch');
        const hotelDestinationList = document.getElementById('hotelDestinationList');

        // Pre-fill from the last search stored in session (e.g. Modify Search on the results page).
        if (hotelDestinationFromSession && hotelDestinationValue) {
            const readableCity = hotelDestinationFromSession
                .split('-')
                .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                .join(' ');
            hotelDestinationDisplay.textContent = readableCity;
            hotelDestinationValue.value = hotelDestinationFromSession;
        }

        if (hotelDestinationBtn) {
            hotelDestinationBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                closeAllHotelDropdowns();
                hotelDestinationDropdown.classList.add('active');
                showOverlay('dropdownOverlay');
                hotelDestinationSearch.focus();
            });

            hotelDestinationSearch.addEventListener('input', (e) => {
                loadHotelDestinations(e.target.value, hotelDestinationList);
            });

            hotelDestinationList.addEventListener('click', (e) => {
                const destItem = e.target.closest('.destination-item');
                if (destItem) {
                    e.stopPropagation();
                    const city = destItem.dataset.name;
                    const country = destItem.dataset.country;
                    hotelDestinationDisplay.textContent = `${city}, ${country}`;
                    hotelDestinationValue.value = city;
                    hotelDestinationSearch.value = '';
                    closeAllHotelDropdowns();
                }
            });
        }

        // Hotel Dates
        // Auto dates for Hotel
        const today = new Date();

        // Check-in = 2 days after today
        const checkInDate = new Date();
        checkInDate.setDate(today.getDate() + 2);

        // Check-out = 4 days after today
        const checkOutDate = new Date();
        checkOutDate.setDate(today.getDate() + 4);
        const hotelCheckinDate = document.getElementById('hotelCheckinDate');
        const hotelCheckoutDate = document.getElementById('hotelCheckoutDate');

        // Prefer the dates already stored in session (Modify Search) over the defaults.
        const initialCheckin = hotelCheckinFromSession || checkInDate;
        const initialCheckout = hotelCheckoutFromSession || checkOutDate;

        if (hotelCheckinDate) {
            const hotelCheckinPicker = flatpickr(hotelCheckinDate, {
                dateFormat: "d-m-Y",
                minDate: "today",
                defaultDate: initialCheckin,
                onChange: function(selectedDates) {
                    if (selectedDates.length > 0) {
                        const checkin = new Date(selectedDates[0]);
                        const autoCheckout = new Date(checkin);
                        autoCheckout.setDate(autoCheckout.getDate() + 2);
                        hotelCheckoutPicker.set('minDate', checkin);
                        hotelCheckoutPicker.setDate(autoCheckout, true);
                    }
                }
            });

            const hotelCheckoutPicker = flatpickr(hotelCheckoutDate, {
                dateFormat: "d-m-Y",
                minDate: initialCheckin,
                defaultDate: initialCheckout
            });

            window.hotelCheckinPicker = hotelCheckinPicker;
            window.hotelCheckoutPicker = hotelCheckoutPicker;
        }

        // Hotel Travelers
        const hotelTravelerBtn = document.getElementById('hotelTravelerBtn');
        const hotelTravelerDropdown = document.getElementById('hotelTravelerDropdown');
        const hotelTravelerDisplay = document.getElementById('hotelTravelerDisplay');
        const hotelApplyTravelerBtn = document.getElementById('hotelApplyTravelerBtn');

        // Sync the visible stepper counts + summary text with whatever hotelTravelers
        // was initialized to above (either the defaults or the session's last search).
        ['adult', 'child', 'room'].forEach(type => {
            const countElement = document.getElementById(`hotel${type.charAt(0).toUpperCase() + type.slice(1)}Count`);
            if (countElement) countElement.textContent = hotelTravelers[type];
        });
        if (hotelTravelerDisplay) {
            const totalTravelers = hotelTravelers.adult + hotelTravelers.child;
            hotelTravelerDisplay.textContent = `${totalTravelers} Travelers, ${hotelTravelers.room} Room${hotelTravelers.room > 1 ? 's' : ''}`;
        }
        renderHotelChildAges();

        if (hotelTravelerBtn) {
            hotelTravelerBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                closeAllHotelDropdowns();
                hotelTravelerDropdown.classList.add('active');
                showOverlay('dropdownOverlay');
            });

            document.querySelectorAll('#hotelTravelerDropdown .traveler-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const type = btn.dataset.type;
                    const action = btn.dataset.action;

                    if (action === 'plus') {
                        hotelTravelers[type]++;
                    } else if (action === 'minus' && hotelTravelers[type] > 0) {
                        if (type === 'adult' && hotelTravelers[type] === 1) return;
                        if (type === 'room' && hotelTravelers[type] === 1) return;
                        hotelTravelers[type]--;
                    }

                    const countElement = document.getElementById(`hotel${type.charAt(0).toUpperCase() + type.slice(1)}Count`);
                    if (countElement) {
                        countElement.textContent = hotelTravelers[type];
                    }

                    if (type === 'child') {
                        renderHotelChildAges();
                    }
                });
            });

            hotelApplyTravelerBtn.addEventListener('click', (e) => {
                e.stopPropagation();

                if (!validateHotelChildAges()) {
                    return;
                }
                hideChildAgeError();

                const adults = hotelTravelers.adult;
                const children = hotelTravelers.child;
                const rooms = hotelTravelers.room;
                const totalTravelers = adults + children;
                const finalText = `${totalTravelers} Travelers, ${rooms} Room${rooms > 1 ? 's' : ''}`;
                hotelTravelerDisplay.textContent = finalText;
                closeAllHotelDropdowns();
            });
        }

        // Hotel Nationality
        const hotelNationalityBtn = document.getElementById('hotelNationalityBtn');
        const hotelCountryDropdown = document.getElementById('hotelCountryDropdown');
        const hotelNationalityDisplay = document.getElementById('hotelNationalityDisplay');
        const hotelNationalityValue = document.getElementById('hotelNationalityValue');
        const hotelCountrySearch = document.getElementById('hotelCountrySearch');
        const hotelCountryList = document.getElementById('hotelCountryList');

        // Pre-fill from session (Modify Search): set the code immediately, then
        // resolve its flag + display name from the countries list.
        if (hotelNationalityFromSession && hotelNationalityValue) {
            hotelNationalityValue.value = hotelNationalityFromSession;
            fetch(`${API_BASE_URL}/api/countries`)
                .then(r => r.json())
                .then(data => {
                    const match = (data.data || []).find(c => c.country_code === hotelNationalityFromSession);
                    if (match && hotelNationalityDisplay) {
                        hotelNationalityDisplay.textContent = `${match.flag} ${match.country}`;
                    }
                })
                .catch(() => {});
        }

        if (hotelNationalityBtn) {
            hotelNationalityBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                closeAllHotelDropdowns();
                hotelCountryDropdown.classList.add('active');
                showOverlay('dropdownOverlay');

                if (!hotelCountryList.querySelector('.country-item')) {
                    hotelCountryList.innerHTML = '<div class="loading">Loading countries...</div>';
                    loadCountries(hotelCountryList, hotelCountrySearch);
                }

                hotelCountrySearch.focus();
            });

            hotelCountryList.addEventListener('click', (e) => {
                const countryItem = e.target.closest('.country-item');
                if (countryItem) {
                    e.stopPropagation();
                    hotelNationalityDisplay.textContent = `${countryItem.dataset.flag} ${countryItem.dataset.name}`;
                    hotelNationalityValue.value = countryItem.dataset.code;
                    hotelCountrySearch.value = '';
                    hotelCountryList.querySelectorAll('.country-item').forEach(i => i.style.display = 'flex');
                    closeAllHotelDropdowns();
                }
            });
        }

        // Hotel Form Submission
        const hotelSearchForm = document.getElementById('hotelSearchForm');
        if (hotelSearchForm) {
            hotelSearchForm.addEventListener('submit', submitHotelForm);
        }
    }

    function submitHotelForm(e) {
        e.preventDefault();

        const child = hotelTravelers.child;
        let childAges = [];

        if (child > 0) {
            if (!validateHotelChildAges(false)) {
                // Open the travelers dropdown first, then reveal the inline message
                // and focus the empty field now that it's actually visible.
                closeAllHotelDropdowns();
                document.getElementById('hotelTravelerDropdown')?.classList.add('active');
                showOverlay('dropdownOverlay');
                validateHotelChildAges(true);
                return false;
            }

            childAges = Array.from(document.querySelectorAll('#hotelChildAgesContainer select[data-child-age-index]'))
                .map(select => select.value);
        }

        // Show hotel loader
        showPageLoader('hotel');

        const hotelDestinationValue = document.getElementById('hotelDestinationValue');
        const hotelCheckinDate = document.getElementById('hotelCheckinDate');
        const hotelCheckoutDate = document.getElementById('hotelCheckoutDate');
        const hotelNationalityValue = document.getElementById('hotelNationalityValue');

        const country = hotelDestinationValue.value.toLowerCase().replace(/\s+/g, '-');
        let checkinDate = window.hotelCheckinPicker && window.hotelCheckinPicker.selectedDates.length > 0
            ? formatDate(window.hotelCheckinPicker.selectedDates[0])
            : hotelCheckinDate.value;
        let checkoutDate = window.hotelCheckoutPicker && window.hotelCheckoutPicker.selectedDates.length > 0
            ? formatDate(window.hotelCheckoutPicker.selectedDates[0])
            : hotelCheckoutDate.value;

        const adult = hotelTravelers.adult;
        const room = hotelTravelers.room;
        const nationality = hotelNationalityValue.value;

        const url = `${API_BASE_URL}/hotels/${country}/${checkinDate}/${checkoutDate}/${adult}/${child}/${room}/${nationality}`;

        // Child ages are stored in the session, not the URL. Save them first,
        // then navigate — search() reads them back out of session.
        fetch(`${API_BASE_URL}/hotels/store-child-ages`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ child_ages: childAges })
        })
            .catch(() => {}) // Navigate regardless — search() just falls back to no ages.
            .finally(() => {
                window.location.href = url;
            });
        return false;
    }

    // ========== FLIGHT FORM INITIALIZATION ==========
    function initializeFlightForm() {
        // Trip Type Toggle
        const roundTripBtn = document.getElementById('roundTripBtn');
        const oneWayBtn = document.getElementById('oneWayBtn');
        const returnDateField = document.getElementById('returnDateField');

        if (roundTripBtn) {
            roundTripBtn.addEventListener('click', () => {
                tripType = 'round';
                roundTripBtn.classList.add('active');
                oneWayBtn.classList.remove('active');
                if (returnDateField) returnDateField.style.display = 'block';
            });

            oneWayBtn.addEventListener('click', () => {
                tripType = 'oneway';
                oneWayBtn.classList.add('active');
                roundTripBtn.classList.remove('active');
                if (returnDateField) returnDateField.style.display = 'none';
            });
        }

        // Flight From
        const flightFromBtn = document.getElementById('flightFromBtn');
        const flightFromDropdown = document.getElementById('flightFromDropdown');
        const flightFromDisplay = document.getElementById('flightFromDisplay');
        const flightFromValue = document.getElementById('flightFromValue');
        const flightFromSearch = document.getElementById('flightFromSearch');
        const flightFromList = document.getElementById('flightFromList');

        if (flightFromBtn) {
            flightFromBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                closeAllFlightDropdowns();
                flightFromDropdown.classList.add('active');
                showOverlay('flightDropdownOverlay');
                flightFromSearch.focus();
            });

            flightFromSearch.addEventListener('input', (e) => {
                loadAirports(e.target.value, flightFromList);
            });

            flightFromList.addEventListener('click', (e) => {
                const airportItem = e.target.closest('.airport-item');
                if (airportItem) {
                    e.stopPropagation();
                    flightFromDisplay.textContent = `${airportItem.dataset.name} (${airportItem.dataset.code})`;
                    flightFromValue.value = airportItem.dataset.code;
                    flightFromSearch.value = '';
                    closeAllFlightDropdowns();
                }
            });
        }

        // Flight To
        const flightToBtn = document.getElementById('flightToBtn');
        const flightToDropdown = document.getElementById('flightToDropdown');
        const flightToDisplay = document.getElementById('flightToDisplay');
        const flightToValue = document.getElementById('flightToValue');
        const flightToSearch = document.getElementById('flightToSearch');
        const flightToList = document.getElementById('flightToList');

        if (flightToBtn) {
            flightToBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                closeAllFlightDropdowns();
                flightToDropdown.classList.add('active');
                showOverlay('flightDropdownOverlay');
                flightToSearch.focus();
            });

            flightToSearch.addEventListener('input', (e) => {
                loadAirports(e.target.value, flightToList);
            });

            flightToList.addEventListener('click', (e) => {
                const airportItem = e.target.closest('.airport-item');
                if (airportItem) {
                    e.stopPropagation();
                    flightToDisplay.textContent = `${airportItem.dataset.name} (${airportItem.dataset.code})`;
                    flightToValue.value = airportItem.dataset.code;
                    flightToSearch.value = '';
                    closeAllFlightDropdowns();
                }
            });
        }

        // Flight Dates
        // Auto dates for Flight
        const flightToday = new Date();

        // Departure = +2 days
        const departureDateAuto = new Date();
        departureDateAuto.setDate(flightToday.getDate() + 2);

        // Return = +4 days
        const returnDateAuto = new Date();
        returnDateAuto.setDate(flightToday.getDate() + 4);

        const flightDepartureDate = document.getElementById('flightDepartureDate');
        const flightReturnDate = document.getElementById('flightReturnDate');

        if (flightDepartureDate) {

        const flightDeparturePicker = flatpickr(flightDepartureDate, {
            dateFormat: "d-m-Y",
            minDate: "today",
            defaultDate: departureDateAuto,
            onChange: function(selectedDates) {
                if (selectedDates.length > 0) {
                    const depart = new Date(selectedDates[0]);
                    const autoReturn = new Date(depart);
                    autoReturn.setDate(autoReturn.getDate() + 2);
                    flightReturnPicker.set('minDate', depart);
                    flightReturnPicker.setDate(autoReturn, true);
                }
            }
        });

        const flightReturnPicker = flatpickr(flightReturnDate, {
            dateFormat: "d-m-Y",
            minDate: departureDateAuto,
            defaultDate: returnDateAuto
        });

            window.flightDeparturePicker = flightDeparturePicker;
            window.flightReturnPicker = flightReturnPicker;
        }

        // Flight Passengers
        const flightPassengerBtn = document.getElementById('flightPassengerBtn');
        const flightPassengerDropdown = document.getElementById('flightPassengerDropdown');
        const flightPassengerDisplay = document.getElementById('flightPassengerDisplay');
        const flightApplyPassengerBtn = document.getElementById('flightApplyPassengerBtn');

        if (flightPassengerBtn) {
            flightPassengerBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                closeAllFlightDropdowns();
                flightPassengerDropdown.classList.add('active');
                showOverlay('flightDropdownOverlay');
            });

            document.querySelectorAll('#flightPassengerDropdown .passenger-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const type = btn.dataset.type;
                    const action = btn.dataset.action;

                    if (action === 'plus') {
                        flightPassengers[type]++;
                    } else if (action === 'minus' && flightPassengers[type] > 0) {
                        if (type === 'adult' && flightPassengers[type] === 1) return;
                        flightPassengers[type]--;
                    }

                    const countElement = document.getElementById(`flight${type.charAt(0).toUpperCase() + type.slice(1)}Count`);
                    if (countElement) {
                        countElement.textContent = flightPassengers[type];
                    }
                });
            });

            flightApplyPassengerBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const total = flightPassengers.adult + flightPassengers.child + flightPassengers.infant;
                const text = total === 1 ? '1 Passenger' : `${total} Passengers`;
                flightPassengerDisplay.textContent = text;
                closeAllFlightDropdowns();
            });
        }

        // Flight Class
        const flightClassBtn = document.getElementById('flightClassBtn');
        const flightClassDropdown = document.getElementById('flightClassDropdown');
        const flightClassDisplay = document.getElementById('flightClassDisplay');
        const flightClassValue = document.getElementById('flightClassValue');
        const flightClassList = document.getElementById('flightClassList');

        if (flightClassBtn) {
            flightClassBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                closeAllFlightDropdowns();
                flightClassDropdown.classList.add('active');
                showOverlay('flightDropdownOverlay');
            });

            flightClassList.querySelectorAll('.class-item').forEach(item => {
                item.addEventListener('click', (e) => {
                    e.stopPropagation();
                    flightClassDisplay.textContent = item.querySelector('.class-name').textContent;
                    flightClassValue.value = item.dataset.class;
                    closeAllFlightDropdowns();
                });
            });
        }

        // Flight Form Submission
        const flightSearchForm = document.getElementById('flightSearchForm');
        if (flightSearchForm) {
            flightSearchForm.addEventListener('submit', submitFlightForm);
        }
    }

    function submitFlightForm(e) {
        e.preventDefault();

        // Show flight loader
        showPageLoader('flight');

        const flightFromValue = document.getElementById('flightFromValue');
        const flightToValue = document.getElementById('flightToValue');
        const flightDepartureDate = document.getElementById('flightDepartureDate');
        const flightReturnDate = document.getElementById('flightReturnDate');
        const flightClassValue = document.getElementById('flightClassValue');

        const origin = flightFromValue.value.toLowerCase();
        const destination = flightToValue.value.toLowerCase();
        const selectedTripType = tripType === 'oneway' ? 'oneway' : 'round';
        const flightClass = flightClassValue.value;

        let departureDate = window.flightDeparturePicker && window.flightDeparturePicker.selectedDates.length > 0
            ? formatDate(window.flightDeparturePicker.selectedDates[0])
            : flightDepartureDate.value;
        let returnDate = null;

        if (selectedTripType === 'round') {
            returnDate = window.flightReturnPicker && window.flightReturnPicker.selectedDates.length > 0
                ? formatDate(window.flightReturnPicker.selectedDates[0])
                : flightReturnDate.value;
        }

        const adult = flightPassengers.adult;
        const child = flightPassengers.child || 0;
        const infant = flightPassengers.infant || 0;

        let url = `${API_BASE_URL}/flights/${origin}/${destination}/${selectedTripType}/${flightClass}/${departureDate}`;

        if (returnDate) {
            url += `/${returnDate}`;
        }

        url += `/${adult}/${child}/${infant}`;

        window.location.href = url;
        return false;
    }

    // ========== UMRAH FORM FUNCTIONS ==========
    let umrahLocations = [];
    let umrahPassengers = {
        adult: 1,
        child: 0,
        infant: 0
    };
    let umrahNights = {
        makkah: 0,
        madina: 0
    };

    async function loadUmrahLocations() {
        try {
            const response = await fetch(`${API_BASE_URL}/api/umrah-locations`);
            const data = await response.json();
            umrahLocations = data;
            return data;
        } catch (error) {
            console.error('Error loading umrah locations:', error);
            return [];
        }
    }

    function renderUmrahLocations(container, locations) {
        if (!container) return;

        container.innerHTML = locations.map(loc => `
            <div class="location-item" data-id="${loc.id}" data-name="${loc.name}">
                <i class="fas fa-map-marker-alt location-icon"></i>
                <div class="location-details">
                    <div class="location-name">${loc.name}</div>
                    <div class="location-country">${loc.country || ''}</div>
                </div>
            </div>
        `).join('');
    }

    function filterUmrahLocations(searchTerm, container) {
        const filtered = umrahLocations.filter(loc =>
            loc.name.toLowerCase().includes(searchTerm.toLowerCase())
        );
        renderUmrahLocations(container, filtered);
    }

    function initializeUmrahForm() {
        // Initialize date pickers
        const umrahDepartureDate = document.getElementById('umrahDepartureDate');
        const umrahReturnDate = document.getElementById('umrahReturnDate');

        // Calculate default dates
        const today = new Date();
        const defaultDeparture = new Date(today);
        defaultDeparture.setDate(today.getDate() + 2);
        const defaultReturn = new Date(today);
        defaultReturn.setDate(today.getDate() + 6);

        if (umrahDepartureDate) {
            window.umrahDeparturePicker = flatpickr(umrahDepartureDate, {
                minDate: "today",
                dateFormat: "Y-m-d",
                defaultDate: umrahDepartureDate.value || defaultDeparture,
                onChange: function(selectedDates) {
                    if (selectedDates.length > 0 && window.umrahReturnPicker) {
                        const returnDate = new Date(selectedDates[0]);
                        returnDate.setDate(returnDate.getDate() + 4);
                        window.umrahReturnPicker.set('minDate', selectedDates[0]);
                        window.umrahReturnPicker.setDate(returnDate);
                    }
                }
            });
        }

        if (umrahReturnDate) {
            window.umrahReturnPicker = flatpickr(umrahReturnDate, {
                minDate: "today",
                dateFormat: "Y-m-d",
                defaultDate: umrahReturnDate.value || defaultReturn
            });
        }

        // Location dropdowns (From/To)
        const umrahFromBtn = document.getElementById('umrahFromBtn');
        const umrahFromDropdown = document.getElementById('umrahFromDropdown');
        const umrahFromSearch = document.getElementById('umrahFromSearch');
        const umrahFromList = document.getElementById('umrahFromList');
        const umrahFromValue = document.getElementById('umrahFromValue');
        const umrahFromDisplay = document.getElementById('umrahFromDisplay');

        const umrahToBtn = document.getElementById('umrahToBtn');
        const umrahToDropdown = document.getElementById('umrahToDropdown');
        const umrahToSearch = document.getElementById('umrahToSearch');
        const umrahToList = document.getElementById('umrahToList');
        const umrahToValue = document.getElementById('umrahToValue');
        const umrahToDisplay = document.getElementById('umrahToDisplay');

        const umrahOverlay = document.getElementById('umrahDropdownOverlay');

        // Add click handlers to airport items
        if (umrahFromList) {
            umrahFromList.addEventListener('click', function(e) {
                const item = e.target.closest('.airport-item');
                if (item) {
                    umrahFromValue.value = item.dataset.code;
                    umrahFromDisplay.textContent = `${item.dataset.name} (${item.dataset.code})`;
                    umrahFromDropdown.classList.remove('active');
                    if (umrahOverlay) umrahOverlay.classList.remove('active');
                }
            });
        }

        if (umrahToList) {
            umrahToList.addEventListener('click', function(e) {
                const item = e.target.closest('.airport-item');
                if (item) {
                    umrahToValue.value = item.dataset.code;
                    umrahToDisplay.textContent = `${item.dataset.name} (${item.dataset.code})`;
                    umrahToDropdown.classList.remove('active');
                    if (umrahOverlay) umrahOverlay.classList.remove('active');
                }
            });
        }

        // From button click
        if (umrahFromBtn) {
            umrahFromBtn.addEventListener('click', function() {
                umrahFromDropdown.classList.toggle('active');
                if (umrahToDropdown) umrahToDropdown.classList.remove('active');
                if (umrahPassengerDropdown) umrahPassengerDropdown.classList.remove('active');
                if (umrahOverlay) umrahOverlay.classList.toggle('active');
                if (umrahFromDropdown.classList.contains('active') && umrahFromSearch) {
                    umrahFromSearch.focus();
                }
            });
        }

        // To button click
        if (umrahToBtn) {
            umrahToBtn.addEventListener('click', function() {
                umrahToDropdown.classList.toggle('active');
                if (umrahFromDropdown) umrahFromDropdown.classList.remove('active');
                if (umrahPassengerDropdown) umrahPassengerDropdown.classList.remove('active');
                if (umrahOverlay) umrahOverlay.classList.toggle('active');
                if (umrahToDropdown.classList.contains('active') && umrahToSearch) {
                    umrahToSearch.focus();
                }
            });
        }

        // Search inputs
        if (umrahFromSearch) {
            umrahFromSearch.addEventListener('input', function() {
                loadAirports(this.value, umrahFromList);
            });
        }

        if (umrahToSearch) {
            umrahToSearch.addEventListener('input', function() {
                loadAirports(this.value, umrahToList);
            });
        }

        // Passenger dropdown
        const umrahPassengerBtn = document.getElementById('umrahPassengerBtn');
        const umrahPassengerDropdown = document.getElementById('umrahPassengerDropdown');
        const umrahPassengerDisplay = document.getElementById('umrahPassengerDisplay');
        const umrahApplyPassengerBtn = document.getElementById('umrahApplyPassengerBtn');

        const umrahAdultCountEl = document.getElementById('umrahAdultCount');
        const umrahChildCountEl = document.getElementById('umrahChildCount');
        const umrahInfantCountEl = document.getElementById('umrahInfantCount');

        if (umrahAdultCountEl) umrahPassengers.adult = parseInt(umrahAdultCountEl.textContent) || 1;
        if (umrahChildCountEl) umrahPassengers.child = parseInt(umrahChildCountEl.textContent) || 0;
        if (umrahInfantCountEl) umrahPassengers.infant = parseInt(umrahInfantCountEl.textContent) || 0;

        if (umrahPassengerBtn) {
            umrahPassengerBtn.addEventListener('click', function() {
                umrahPassengerDropdown.classList.toggle('active');
                if (umrahFromDropdown) umrahFromDropdown.classList.remove('active');
                if (umrahToDropdown) umrahToDropdown.classList.remove('active');
                if (umrahOverlay) umrahOverlay.classList.toggle('active');
            });
        }

        // Passenger counter buttons
        const umrahPassengerBtns = document.querySelectorAll('#umrahPassengerDropdown .passenger-btn');
        umrahPassengerBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const type = this.dataset.type;
                const action = this.dataset.action;

                if (type === 'adult') {
                    if (action === 'plus') umrahPassengers.adult++;
                    else if (action === 'minus' && umrahPassengers.adult > 1) umrahPassengers.adult--;
                    if (umrahAdultCountEl) umrahAdultCountEl.textContent = umrahPassengers.adult;
                } else if (type === 'child') {
                    if (action === 'plus') umrahPassengers.child++;
                    else if (action === 'minus' && umrahPassengers.child > 0) umrahPassengers.child--;
                    if (umrahChildCountEl) umrahChildCountEl.textContent = umrahPassengers.child;
                } else if (type === 'infant') {
                    if (action === 'plus' && umrahPassengers.infant < umrahPassengers.adult) umrahPassengers.infant++;
                    else if (action === 'minus' && umrahPassengers.infant > 0) umrahPassengers.infant--;
                    if (umrahInfantCountEl) umrahInfantCountEl.textContent = umrahPassengers.infant;
                }
            });
        });

        if (umrahApplyPassengerBtn) {
            umrahApplyPassengerBtn.addEventListener('click', function() {
                const total = umrahPassengers.adult + umrahPassengers.child + umrahPassengers.infant;
                if (umrahPassengerDisplay) {
                    umrahPassengerDisplay.innerHTML = `${total} ${total === 1 ? 'Passenger' : 'Passengers'}`;
                }
                if (umrahPassengerDropdown) umrahPassengerDropdown.classList.remove('active');
                if (umrahOverlay) umrahOverlay.classList.remove('active');
            });
        }

        // Nights counters
        const makkahNightsValue = document.getElementById('makkahNightsValue');
        const madinaNightsValue = document.getElementById('madinaNightsValue');
        const makkahNightsDisplay = document.getElementById('makkahNightsDisplay');
        const madinaNightsDisplay = document.getElementById('madinaNightsDisplay');

        if (makkahNightsValue) umrahNights.makkah = parseInt(makkahNightsValue.value) || 0;
        if (madinaNightsValue) umrahNights.madina = parseInt(madinaNightsValue.value) || 0;

        const makkahPlus = document.getElementById('makkahPlus');
        const makkahMinus = document.getElementById('makkahMinus');
        const madinaPlus = document.getElementById('madinaPlus');
        const madinaMinus = document.getElementById('madinaMinus');

        if (makkahPlus) {
            makkahPlus.addEventListener('click', function() {
                umrahNights.makkah++;
                if (makkahNightsDisplay) makkahNightsDisplay.textContent = umrahNights.makkah;
                if (makkahNightsValue) makkahNightsValue.value = umrahNights.makkah;
            });
        }

        if (makkahMinus) {
            makkahMinus.addEventListener('click', function() {
                if (umrahNights.makkah > 0) {
                    umrahNights.makkah--;
                    if (makkahNightsDisplay) makkahNightsDisplay.textContent = umrahNights.makkah;
                    if (makkahNightsValue) makkahNightsValue.value = umrahNights.makkah;
                }
            });
        }

        if (madinaPlus) {
            madinaPlus.addEventListener('click', function() {
                umrahNights.madina++;
                if (madinaNightsDisplay) madinaNightsDisplay.textContent = umrahNights.madina;
                if (madinaNightsValue) madinaNightsValue.value = umrahNights.madina;
            });
        }

        if (madinaMinus) {
            madinaMinus.addEventListener('click', function() {
                if (umrahNights.madina > 0) {
                    umrahNights.madina--;
                    if (madinaNightsDisplay) madinaNightsDisplay.textContent = umrahNights.madina;
                    if (madinaNightsValue) madinaNightsValue.value = umrahNights.madina;
                }
            });
        }

        // Overlay click
        if (umrahOverlay) {
            umrahOverlay.addEventListener('click', function() {
                if (umrahFromDropdown) umrahFromDropdown.classList.remove('active');
                if (umrahToDropdown) umrahToDropdown.classList.remove('active');
                if (umrahPassengerDropdown) umrahPassengerDropdown.classList.remove('active');
                umrahOverlay.classList.remove('active');
            });
        }
    }

    function submitUmrahForm(e) {
        e.preventDefault();

        const umrahFromValue = document.getElementById('umrahFromValue');
        const umrahToValue = document.getElementById('umrahToValue');
        const umrahDepartureDate = document.getElementById('umrahDepartureDate');
        const umrahReturnDate = document.getElementById('umrahReturnDate');
        const makkahNightsValue = document.getElementById('makkahNightsValue');
        const madinaNightsValue = document.getElementById('madinaNightsValue');

        const formData = {
            origin: umrahFromValue ? umrahFromValue.value : '',
            destination: umrahToValue ? umrahToValue.value : '',
            departure_date: umrahDepartureDate ? umrahDepartureDate.value : '',
            return_date: umrahReturnDate ? umrahReturnDate.value : '',
            adults: umrahPassengers.adult,
            children: umrahPassengers.child,
            infants: umrahPassengers.infant,
            makkah_nights: makkahNightsValue ? parseInt(makkahNightsValue.value) : 0,
            madina_nights: madinaNightsValue ? parseInt(madinaNightsValue.value) : 0
        };

        // Validate required fields
        if (!formData.origin) {
            alert('Please select departure location');
            return false;
        }

        if (!formData.destination) {
            alert('Please select destination');
            return false;
        }

        if (!formData.departure_date) {
            alert('Please select departure date');
            return false;
        }

        if (!formData.return_date) {
            alert('Please select return date');
            return false;
        }

        // Build URL in the format: /umrah/{origin}/{destination}/{departureDate}/{returnDate}/{adult}/{child}/{infant}
        // Convert date from YYYY-MM-DD to DD-MM-YYYY
        const formatDate = (date) => date.split('-').reverse().join('-');

        let url = `${API_BASE_URL}/umrah/${formData.origin.toLowerCase()}/${formData.destination.toLowerCase()}/${formatDate(formData.departure_date)}`;

        if (formData.return_date) {
            url += `/${formatDate(formData.return_date)}`;
        }

        url += `/${formData.adults}/${formData.children}/${formData.infants}/${formData.makkah_nights}/${formData.madina_nights}`;

        window.location.href = url;

        return false;
    }

    // ========== TOURS FORM INITIALIZATION ==========
    function initializeToursForm() {
        // DOM Elements
        const destinationBtn = document.getElementById('toursDestinationBtn');
        const destinationDropdown = document.getElementById('toursDestinationDropdown');
        const destinationDisplay = document.getElementById('toursDestinationDisplay');
        const destinationValue = document.getElementById('toursDestinationValue');
        const destinationSearch = document.getElementById('toursDestinationSearch');
        const destinationList = document.getElementById('toursDestinationList');
        const overlay = document.getElementById('toursDropdownOverlay');

        const tourTypeBtn = document.getElementById('tourTypeBtn');
        const tourTypeDropdown = document.getElementById('tourTypeDropdown');
        const tourTypeDisplay = document.getElementById('tourTypeDisplay');
        const tourTypeValue = document.getElementById('tourTypeValue');
        const tourTypeList = document.getElementById('tourTypeList');

        const toursTravelerBtn = document.getElementById('toursTravelerBtn');
        const toursTravelerDropdown = document.getElementById('toursTravelerDropdown');
        const toursTravelerDisplay = document.getElementById('toursTravelerDisplay');
        const toursApplyTravelerBtn = document.getElementById('toursApplyTravelerBtn');

        // Close all dropdowns function
        function closeAllToursDropdowns() {
            if (destinationDropdown) destinationDropdown.classList.remove('active');
            if (tourTypeDropdown) tourTypeDropdown.classList.remove('active');
            if (toursTravelerDropdown) toursTravelerDropdown.classList.remove('active');
            if (overlay) overlay.classList.remove('active');
        }

        // Load destinations from API
        async function loadToursDestinations(searchTerm) {
            if (searchTerm.length < 3) {
                destinationList.innerHTML = '<div class="loading">{{t("tourform.type_3_characters")}}</div>';
                return;
            }

            destinationList.innerHTML = '<div class="loading">{{t("tourform.searching")}}</div>';

            try {
                const response = await fetch(`${API_BASE_URL}/api/hotel_destinations?search=${searchTerm}`);
                const data = await response.json();

                if (data.success && data.data && data.data.length > 0) {
                    destinationList.innerHTML = '';
                    data.data.forEach(destination => {
                        const destItem = document.createElement('div');
                        destItem.className = 'destination-item';
                        destItem.dataset.name = destination.city;
                        destItem.dataset.country = destination.country;
                        destItem.innerHTML = `
                            <i class="fas fa-map-marker-alt destination-icon"></i>
                            <div class="destination-details">
                                <div class="destination-name">${destination.city}</div>
                                <div class="destination-type">${destination.country}</div>
                            </div>
                        `;
                        destinationList.appendChild(destItem);
                    });
                } else {
                    destinationList.innerHTML = '<div class="loading">{{t("tourform.no_destinations_found")}}</div>';
                }
            } catch (error) {
                console.error('Error loading destinations:', error);
                destinationList.innerHTML = '<div class="loading">{{t("tourform.error_loading")}}</div>';
            }
        }

        // Load tour types from API
        async function loadTourTypes() {
            try {
                const response = await fetch(`${API_BASE_URL}/api/tour-package-types`);
                const data = await response.json();

                if (data.success && data.data && data.data.length > 0) {
                    tourTypeList.innerHTML = '';

                    // Add "All tour" option
                    const allItem = document.createElement('div');
                    allItem.className = 'tour-type-item';
                    allItem.dataset.type = 'all';
                    allItem.innerHTML = `
                        <i class="fas fa-globe text-gray-500"></i>
                        <span class="tour-type-name">{{t("tourform.all_tour")}}</span>
                    `;
                    tourTypeList.appendChild(allItem);

                    // Add tour types from database
                    data.data.forEach(tourType => {
                        const typeItem = document.createElement('div');
                        typeItem.className = 'tour-type-item';
                        typeItem.dataset.type = tourType.id;
                        typeItem.innerHTML = `
                            <i class="fas fa-tag text-gray-500"></i>
                            <span class="tour-type-name">${tourType.name}</span>
                        `;
                        tourTypeList.appendChild(typeItem);
                    });
                }
            } catch (error) {
                console.error('Error loading tour types:', error);
                tourTypeList.innerHTML = '<div class="loading">{{t("tourform.error_loading_types")}}</div>';
            }
        }

        // Destination dropdown
        if (destinationBtn) {
            destinationBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                closeAllToursDropdowns();
                destinationDropdown.classList.add('active');
                overlay.classList.add('active');
                destinationSearch.focus();
            });

            destinationSearch.addEventListener('input', function() {
                loadToursDestinations(this.value);
            });

            destinationList.addEventListener('click', function(e) {
                const item = e.target.closest('.destination-item');
                if (item) {
                    e.stopPropagation();
                    const city = item.dataset.name;
                    const country = item.dataset.country;
                    destinationDisplay.textContent = `${city}, ${country}`;
                    destinationValue.value = city;
                    destinationSearch.value = '';
                    closeAllToursDropdowns();
                }
            });
        }

        // Tour Type dropdown
        if (tourTypeBtn) {
            tourTypeBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                closeAllToursDropdowns();
                tourTypeDropdown.classList.add('active');
                overlay.classList.add('active');

                // Load tour types if not already loaded
                if (!tourTypeList.querySelector('.tour-type-item')) {
                    loadTourTypes();
                }
            });

            tourTypeList.addEventListener('click', function(e) {
                const item = e.target.closest('.tour-type-item');
                if (item) {
                    e.stopPropagation();
                    const type = item.dataset.type;
                    const name = item.querySelector('.tour-type-name').textContent;
                    tourTypeDisplay.textContent = name;
                    tourTypeValue.value = type;
                    closeAllToursDropdowns();
                }
            });
        }

        // Travelers dropdown
        if (toursTravelerBtn) {
            toursTravelerBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                closeAllToursDropdowns();
                toursTravelerDropdown.classList.add('active');
                overlay.classList.add('active');
            });

            document.querySelectorAll('#toursTravelerDropdown .tours-traveler-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const type = btn.dataset.type;
                    const action = btn.dataset.action;

                    if (action === 'plus') {
                        toursTravelers[type]++;
                    } else if (action === 'minus' && toursTravelers[type] > 0) {
                        if (type === 'adult' && toursTravelers[type] === 1) return;
                        toursTravelers[type]--;
                    }

                    const countElement = document.getElementById(`tours${type.charAt(0).toUpperCase() + type.slice(1)}Count`);
                    if (countElement) {
                        countElement.textContent = toursTravelers[type];
                    }
                });
            });

            toursApplyTravelerBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                const adults = toursTravelers.adult;
                const children = toursTravelers.child;
                const finalText = `${adults} {{t("tourform.adult")}}, ${children} {{t("tourform.child")}}`;
                toursTravelerDisplay.textContent = finalText;
                closeAllToursDropdowns();
            });
        }

        // Close on overlay click
        if (overlay) {
            overlay.addEventListener('click', closeAllToursDropdowns);
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            const clickedInside = e.target.closest('.location-wrapper') ||
                                 e.target.closest('#tourTypeBtn') ||
                                 e.target.closest('#toursTravelerBtn');
            if (!clickedInside) {
                closeAllToursDropdowns();
            }
        });

        // Initialize Flatpickr for date selection
        if (typeof flatpickr !== 'undefined') {
            var sessionStart = '{{ isset($tour_search["original_start_date"]) ? $tour_search["original_start_date"] : "" }}';
            var sessionEnd = '{{ isset($tour_search["original_end_date"]) ? $tour_search["original_end_date"] : "" }}';

            var defaultDates;
            if (sessionStart && sessionEnd) {
                var sParts = sessionStart.split('-');
                var eParts = sessionEnd.split('-');
                defaultDates = [
                    new Date(sParts[2], sParts[1] - 1, sParts[0]),
                    new Date(eParts[2], eParts[1] - 1, eParts[0])
                ];
            } else {
                var today = new Date();
                var startDate = new Date();
                startDate.setDate(today.getDate() + 2);
                var endDate = new Date();
                endDate.setDate(today.getDate() + 4);
                defaultDates = [startDate, endDate];
            }

            window.toursDatePicker = flatpickr('#toursDate', {
                mode: 'range',
                dateFormat: 'd-m-Y',
                minDate: 'today',
                defaultDate: defaultDates,
                locale: {
                    rangeSeparator: ' to '
                },
                showMonths: window.innerWidth >= 768 ? 2 : 1,
                static: false
            });
        }
    }

    function submitToursForm(event) {
        event.preventDefault();

        const destination = document.getElementById('toursDestinationValue').value;
        const dateValue = document.getElementById('toursDate').value;
        const tourType = document.getElementById('tourTypeValue').value;

        if (!destination) {
            alert('{{t("tourform.select_destination")}}');
            return false;
        }

        if (!dateValue) {
            alert('{{t("tourform.select_dates")}}');
            return false;
        }

        const dates = dateValue.split(' to ');
        const startDate = dates[0];
        const endDate = dates[1] || startDate;

        const adult = toursTravelers.adult;
        const child = toursTravelers.child;

        const destinationSlug = destination.toLowerCase().replace(/\s+/g, '-');

        const url = `${API_BASE_URL}/tours/${destinationSlug}/${tourType}/${startDate}/${endDate}/${adult}/${child}`;

        console.log('Submitting to:', url);
        window.location.href = url;

        return false;
    }

    // ========== GLOBAL CLICK HANDLERS ==========
    document.addEventListener('click', (e) => {
        const clickedInsideDropdown = e.target.closest('.destination-dropdown, .traveler-dropdown, .country-dropdown, .airport-dropdown, .passenger-dropdown, .class-dropdown');
        const clickedButton = e.target.closest('[id*="Btn"]');

        if (!clickedInsideDropdown && !clickedButton) {
            closeAllHotelDropdowns();
            closeAllFlightDropdowns();

            // Close umrah dropdowns
            const umrahFromDropdown = document.getElementById('umrahFromDropdown');
            const umrahToDropdown = document.getElementById('umrahToDropdown');
            const umrahPassengerDropdown = document.getElementById('umrahPassengerDropdown');
            const umrahOverlay = document.getElementById('umrahDropdownOverlay');

            if (umrahFromDropdown) umrahFromDropdown.classList.remove('active');
            if (umrahToDropdown) umrahToDropdown.classList.remove('active');
            if (umrahPassengerDropdown) umrahPassengerDropdown.classList.remove('active');
            if (umrahOverlay) umrahOverlay.classList.remove('active');
        }
    });

    // Overlay click handlers
    const dropdownOverlay = document.getElementById('dropdownOverlay');
    const flightDropdownOverlay = document.getElementById('flightDropdownOverlay');
    const umrahDropdownOverlay = document.getElementById('umrahDropdownOverlay');

    if (dropdownOverlay) {
        dropdownOverlay.addEventListener('click', closeAllHotelDropdowns);
    }

    if (flightDropdownOverlay) {
        flightDropdownOverlay.addEventListener('click', closeAllFlightDropdowns);
    }

    if (umrahDropdownOverlay) {
        umrahDropdownOverlay.addEventListener('click', function() {
            const umrahFromDropdown = document.getElementById('umrahFromDropdown');
            const umrahToDropdown = document.getElementById('umrahToDropdown');
            const umrahPassengerDropdown = document.getElementById('umrahPassengerDropdown');

            if (umrahFromDropdown) umrahFromDropdown.classList.remove('active');
            if (umrahToDropdown) umrahToDropdown.classList.remove('active');
            if (umrahPassengerDropdown) umrahPassengerDropdown.classList.remove('active');
            umrahDropdownOverlay.classList.remove('active');
        });
    }

    // ========== INITIALIZE ON DOM READY ==========
    document.addEventListener('DOMContentLoaded', function() {
        initializeTabs();
        initializeHotelForm();
        initializeFlightForm();
        initializeUmrahForm();
        initializeToursForm();

        // Hide page loader when page is fully loaded
        hidePageLoader();
    });

    // ========== PAGE LOADER FUNCTIONS ==========
    function showPageLoader(loaderType = null) {
        const loader = document.getElementById('pageLoader');
        const loaderImage = document.getElementById('loaderImage');

        if (loader) {
            loader.classList.remove('hidden');

            // Change loader image based on type
            if (loaderImage && loaderType) {
                const baseUrl = API_BASE_URL;
                if (loaderType === 'flight') {
                    loaderImage.src = baseUrl + '/public/assets/images/settings/flight-loader.gif';
                } else if (loaderType === 'hotel') {
                    loaderImage.src = baseUrl + '/public/assets/images/settings/hotel-loader.gif';
                }
            }
        }
    }

    function hidePageLoader() {
        const loader = document.getElementById('pageLoader');
        if (loader) {
            loader.classList.add('hidden');
        }
    }

    // Check if current page is hotel or flight related
    function isHotelOrFlightPage() {
        const path = window.location.pathname.toLowerCase();
        return path.includes('/hotel') ||
               path.includes('/hotels') ||
               path.includes('/flight') ||
               path.includes('/flights');
    }

    // Hide loader when page is fully loaded (ALL PAGES)
    window.addEventListener('load', function() {
        hidePageLoader();
    });

    // Hide loader on back/forward button (bfcache restore)
    window.addEventListener('pageshow', function(event) {
        hidePageLoader();
    });

    // Show loader on page navigation (ALL PAGES)
    window.addEventListener('beforeunload', function() {
        showPageLoader();
    });

    // Show loader on all link clicks
    document.addEventListener('DOMContentLoaded', function() {
        // Add click event to all links
        document.querySelectorAll('a:not([target="_blank"]):not([href^="#"]):not([href^="javascript:"]):not([href^="mailto:"]):not([href^="tel:"])').forEach(function(link) {
            link.addEventListener('click', function(e) {
                // Don't show loader for dropdown items or if it's already showing
                if (!this.closest('.select2-results') && !this.closest('.dropdown-menu')) {
                    showPageLoader();
                }
            });
        });

        // Show loader on form submissions (except newsletter)
        document.querySelectorAll('form:not(#newsletterForm)').forEach(function(form) {
            form.addEventListener('submit', function() {
                showPageLoader();
            });
        });
    });

    // ========== NEWSLETTER SUBSCRIPTION ==========
    document.addEventListener('DOMContentLoaded', function() {
        const newsletterForm = document.getElementById('newsletterForm');

        if (newsletterForm) {
            newsletterForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const emailInput = document.getElementById('newsletterEmail');
                const submitBtn = document.getElementById('newsletterBtn');
                const btnText = document.getElementById('newsletterBtnText');
                const email = emailInput.value;

                // Disable button and show loading
                submitBtn.disabled = true;
                btnText.textContent = 'Subscribing...';

                // Get CSRF token
                const csrfToken = document.querySelector('input[name="_token"]').value;

                // Send AJAX request
                fetch('<?= url('/newsletter/subscribe') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email: email })
                })
                .then(response => response.json())
                .then(data => {
                    // Re-enable button
                    submitBtn.disabled = false;
                    btnText.textContent = 'Subscribe Now';

                    // Show popup message
                    if (data.success) {
                        showNewsletterPopup('Success!', data.message, 'success');
                        emailInput.value = ''; // Clear input
                    } else {
                        showNewsletterPopup('Already Subscribed', data.message, 'warning');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    submitBtn.disabled = false;
                    btnText.textContent = 'Subscribe Now';
                    showNewsletterPopup('Error', 'Something went wrong. Please try again later.', 'error');
                });
            });
        }
    });

    // Newsletter popup function
    function showNewsletterPopup(title, message, type) {
        // Create popup overlay
        const overlay = document.createElement('div');
        overlay.className = 'newsletter-popup-overlay';
        overlay.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 99999;';

        // Determine icon and colors based on type
        let icon = '';
        let iconColor = '';
        let borderColor = '';

        if (type === 'success') {
            icon = '<i class="fas fa-check-circle" style="font-size: 4rem;"></i>';
            iconColor = '#10b981';
            borderColor = '#10b981';
        } else if (type === 'warning') {
            icon = '<i class="fas fa-exclamation-circle" style="font-size: 4rem;"></i>';
            iconColor = '#f59e0b';
            borderColor = '#f59e0b';
        } else if (type === 'error') {
            icon = '<i class="fas fa-times-circle" style="font-size: 4rem;"></i>';
            iconColor = '#ef4444';
            borderColor = '#ef4444';
        }

        // Create popup content
        const popup = document.createElement('div');
        popup.className = 'newsletter-popup';
        popup.style.cssText = `background: white; padding: 2.5rem; border-radius: 1rem; max-width: 400px; width: 90%; text-align: center; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); border-top: 4px solid ${borderColor}; animation: popupSlideIn 0.3s ease-out;`;

        popup.innerHTML = `
            <div style="color: ${iconColor}; margin-bottom: 1.5rem;">
                ${icon}
            </div>
            <h3 style="color: #1f2937; font-size: 1.5rem; font-weight: bold; margin-bottom: 0.75rem;">${title}</h3>
            <p style="color: #6b7280; margin-bottom: 1.5rem; line-height: 1.6;">${message}</p>
            <button onclick="this.closest('.newsletter-popup-overlay').remove()" style="background: ${borderColor}; color: white; padding: 0.75rem 2rem; border: none; border-radius: 0.5rem; font-weight: 600; cursor: pointer; transition: all 0.3s; width: 100%;">
                OK
            </button>
        `;

        // Add animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes popupSlideIn {
                from {
                    opacity: 0;
                    transform: translateY(-20px) scale(0.95);
                }
                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }
        `;
        document.head.appendChild(style);

        overlay.appendChild(popup);
        document.body.appendChild(overlay);

        // Close on overlay click
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                overlay.remove();
            }
        });

        // Auto close after 5 seconds
        setTimeout(() => {
            if (document.body.contains(overlay)) {
                overlay.remove();
            }
        }, 5000);
    }

</script>

@include('common.promo-float')

</body>
</html>

