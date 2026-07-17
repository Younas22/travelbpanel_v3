<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{t('flightinvoice.invoice')}}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">

<div class="max-w-4xl mx-auto p-4">
    <!-- Action Buttons -->
    <div class="flex gap-2 mb-4 justify-end print:hidden">
            <!-- Home Button -->
    <button onclick="window.location.href='/'" class="px-3 py-2 border border-gray-300 bg-white text-gray-800 rounded-lg font-semibold text-xs hover:border-blue-600 hover:bg-blue-50 hover:text-blue-600 transition flex items-center gap-1.5">
        <i class="fas fa-home"></i> {{t('flightinvoice.home')}}
    </button>

        <button onclick="window.print()" class="px-3 py-2 border border-gray-300 bg-white text-gray-800 rounded-lg font-semibold text-xs hover:border-blue-600 hover:bg-blue-50 hover:text-blue-600 transition flex items-center gap-1.5">
            <i class="fas fa-print"></i> {{t('flightinvoice.print')}}
        </button>
        <!-- <button onclick="downloadPDF()" class="px-3 py-2 border border-gray-300 bg-white text-gray-800 rounded-lg font-semibold text-xs hover:border-blue-600 hover:bg-blue-50 hover:text-blue-600 transition flex items-center gap-1.5">
            <i class="fas fa-download"></i> {{t('flightinvoice.downloadPdf')}}
        </button> -->
        <button onclick="sendEmail()" style="background-color: #0077BE;" class="px-3 py-2 text-white rounded-lg font-semibold text-xs transition shadow-lg flex items-center gap-1.5 hover:opacity-90">
            <i class="fas fa-envelope"></i> {{t('flightinvoice.sendEmail')}}
        </button>
    </div>

    @php
        $segmentData = is_array($booking->booking_flight_segment) ? $booking->booking_flight_segment : json_decode($booking->booking_flight_segment, true);
        $guestData = is_array($booking->booking_guest) ? $booking->booking_guest : json_decode($booking->booking_guest, true);
        $responseError = $booking->booking_response_error ? (is_array($booking->booking_response_error) ? $booking->booking_response_error : json_decode($booking->booking_response_error, true)) : null;
    @endphp

    <!-- Invoice -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-white border-b-4 border-blue-600 p-5" style="border-color: #0077BE;">
            <div class="flex justify-between items-start mb-4">
                <div class="flex items-center gap-3">
                    <a href={{url('/')}}>
                    <img src="{{ getSettingImage('business_logo','branding') }}"
                         alt="FlightHub Logo"
                         class="h-16 w-auto object-contain">
                    </a>
                </div>

                <div class="text-right">
                    <p class="text-xs text-gray-600 uppercase">{{t('flightinvoice.invoice')}}</p>
                    <strong class="text-base font-bold text-gray-800 block">#INV-{{ $booking->booking_code_ref }}</strong>
                    <p class="text-xs text-gray-600">{{ $booking->created_at->format('d M Y') }}</p>
                </div>
            </div>

            <!-- Status Badges -->
            <div class="flex items-center justify-between pt-3 border-t border-gray-200">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-gray-700">{{t('flightinvoice.bookingStatus')}}:</span>

                    @if(strtolower($booking->booking_status_flag) == 'confirmed')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-green-100 text-green-800 border border-green-300 rounded font-bold text-xs">
                            <i class="fas fa-check-circle"></i>
                            {{t('flightinvoice.confirmed')}}
                        </span>
                    @elseif(strtolower($booking->booking_status_flag) == 'pending')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-yellow-100 text-yellow-800 border border-yellow-300 rounded font-bold text-xs">
                            <i class="fas fa-clock"></i>
                            {{t('flightinvoice.pending')}}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-gray-100 text-gray-800 border border-gray-300 rounded font-bold text-xs">
                            <i class="fas fa-info-circle"></i>
                            {{ ucfirst($booking->booking_status_flag) }}
                        </span>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-gray-700">{{t('flightinvoice.paymentStatus')}}:</span>

                    @if(strtolower($booking->booking_payment_state) == 'paid')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-green-100 text-green-800 border border-green-300 rounded font-bold text-xs">
                            <i class="fas fa-check-circle"></i>
                            {{t('flightinvoice.completed')}}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-yellow-100 text-yellow-800 border border-yellow-300 rounded font-bold text-xs">
                            <i class="fas fa-clock"></i>
                            {{ ucfirst($booking->booking_payment_state) }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="p-5">

            @if(!empty($responseError))
                <div class="bg-red-50 border-l-4 border-red-500 p-3 mb-4 rounded text-sm">
                    <h5 class="font-bold text-red-800">{{ is_array($responseError) ? $responseError[0]['title'] ?? t('flightinvoice.error') : t('flightinvoice.error') }}</h5>
                    <h6 class="text-red-700 text-xs">{{ is_array($responseError) ? $responseError[0]['detail'] ?? 'Unknown error' : $responseError }}</h6>
                </div>
            @endif

            <!-- Booking Details -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-3 mb-4">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                    <div>
                        <div class="text-gray-600 font-semibold mb-0.5">{{t('flightinvoice.bookingRef')}}</div>
                        <div class="font-bold text-gray-800">{{ $booking->booking_code_ref }}</div>
                    </div>
                        <div>
                            <div class="text-gray-600 font-semibold mb-0.5">{{t('flightinvoice.bookingpnr')}}</div>
                            <div class="font-bold text-gray-800">{{$booking->booking_air_pnr}}</div>
                        </div>

                    <div>
                        <div class="text-gray-600 font-semibold mb-0.5">{{t('flightinvoice.departure')}}</div>
                        <div class="font-bold text-gray-800">{{$segmentData['segments'][0][0]['departure']['date_convert']}}</div>
                    </div>
                    @if(count($segmentData['segments']) == 2)
                    <div>
                        <div class="text-gray-600 font-semibold mb-0.5">{{t('flightinvoice.return')}}</div>
                        <div class="font-bold text-gray-800">{{$segmentData['segments'][1][0]['departure']['date_convert']}}</div>
                    </div>
                    @endif
                    <div>
                        <div class="text-gray-600 font-semibold mb-0.5">{{t('flightinvoice.passengers')}}</div>
                        <div class="font-bold text-gray-800">{{count($guestData)}}</div>
                    </div>
                </div>
            </div>

            <!-- Flight Details -->
            <div class="mb-4">
                <h3 class="text-xs font-bold text-blue-900 uppercase tracking-wider mb-2 pb-1 border-b border-gray-200">{{t('flightinvoice.flightDetails')}}</h3>
                @if(!empty($segmentData['segments']) && is_array($segmentData['segments']))
                    @foreach($segmentData['segments'] as $segmentGroup)
                        @if(is_array($segmentGroup))
                            @foreach($segmentGroup as $segment)
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 mb-2 grid grid-cols-[auto_1fr] gap-3 items-center">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-900 to-blue-700 rounded-lg flex items-center justify-center text-white font-bold text-xs">
                        {{ $segment['carrier']['marketing'] ?? t('flightinvoice.notApplicable') }}
                    </div>
                    <div>
                        <div class="font-bold text-blue-900 text-xs mb-0.5">{{ $segment['departure']['airport'] ?? t('flightinvoice.notApplicable') }} → {{ $segment['arrival']['airport'] ?? t('flightinvoice.notApplicable') }}</div>
                        <div class="text-[10px] text-gray-600">{{t('flightinvoice.flight')}} {{ $segment['flight_number'] ?? t('flightinvoice.notApplicable') }} | {{ $segment['departure']['time'] ?? t('flightinvoice.notApplicable') }} - {{ $segment['arrival']['time'] ?? t('flightinvoice.notApplicable') }} | {{ $segment['airline_name'] ?? t('flightinvoice.notApplicable') }}</div>
                    </div>
                </div>
                            @endforeach
                        @endif
                    @endforeach
                @endif
            </div>

            <!-- Traveller Details Table -->
            <div class="mb-4">
                <h3 class="text-xs font-bold text-blue-900 uppercase tracking-wider mb-2 pb-1 border-b border-gray-200">{{t('flightinvoice.travellersInfo')}}</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-[10px] border-collapse">
                        <thead>
                            <tr class="bg-blue-50 border border-gray-300">
                                <th class="border border-gray-300 px-2 py-1.5 text-left font-bold text-blue-900">{{t('flightinvoice.no')}}</th>
                                <th class="border border-gray-300 px-2 py-1.5 text-left font-bold text-blue-900">{{t('flightinvoice.sr')}}</th>
                                <th class="border border-gray-300 px-2 py-1.5 text-left font-bold text-blue-900">{{t('flightinvoice.name')}}</th>
                                <th class="border border-gray-300 px-2 py-1.5 text-left font-bold text-blue-900">{{t('flightinvoice.passportNo')}}</th>
                                <th class="border border-gray-300 px-2 py-1.5 text-left font-bold text-blue-900">{{t('flightinvoice.passportValidity')}}</th>
                                <th class="border border-gray-300 px-2 py-1.5 text-left font-bold text-blue-900">{{t('flightinvoice.dateOfBirth')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(!empty($guestData) && is_array($guestData))
                                @foreach($guestData as $index => $guest)
                            <tr class="border border-gray-300 hover:bg-gray-50">
                                <td class="border border-gray-300 px-2 py-1.5 font-semibold">{{ $index + 1 }}</td>
                                <td class="border border-gray-300 px-2 py-1.5">
                                    @if($guest['gender'] == 'm')
                                        {{t('flightinvoice.mr')}}
                                    @elseif($guest['gender'] == 'f')
                                        {{t('flightinvoice.miss')}}
                                    @else
                                        {{t('flightinvoice.mx')}}
                                    @endif
                                </td>
                                <td class="border border-gray-300 px-2 py-1.5 font-semibold">
                                    {{ $guest['first_name'] ?? t('flightinvoice.notApplicable') }} {{ $guest['last_name'] ?? t('flightinvoice.notApplicable') }}
                                </td>
                                <td class="border border-gray-300 px-2 py-1.5">{{ $guest['passport'] ?? t('flightinvoice.notApplicable') }}</td>
                                <td class="border border-gray-300 px-2 py-1.5">
                                    {{ $guest['passport_issuance_day'] ?? t('flightinvoice.notApplicable') }}-{{ $guest['passport_issuance_month'] ?? t('flightinvoice.notApplicable') }}-{{ $guest['passport_issuance_year'] ?? t('flightinvoice.notApplicable') }} — {{ $guest['passport_issuance_day'] ?? t('flightinvoice.notApplicable') }}-{{ $guest['passport_month_expiry'] ?? t('flightinvoice.notApplicable') }}-{{ $guest['passport_year_expiry'] ?? t('flightinvoice.notApplicable') }}
                                </td>
                                <td class="border border-gray-300 px-2 py-1.5">{{ $guest['dob_day'] ?? t('flightinvoice.notApplicable') }}-{{ $guest['dob_month'] ?? t('flightinvoice.notApplicable') }}-{{ $guest['dob_year'] ?? t('flightinvoice.notApplicable') }}</td>
                            </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Payment & Price Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
                @if($booking->booking_status_flag != "confirmed" && $booking->booking_payment_status != "paid" && $booking->booking_payment_gateway !="after_pay")
                <!-- Payment Method Dropdown -->
                <div>
                    <h3 class="text-xs font-bold text-blue-900 uppercase tracking-wider mb-2 pb-1 border-b border-gray-200">{{t('flightinvoice.selectPaymentMethod')}}</h3>
                    <select id="paymentMethod" class="w-full px-3 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm font-medium text-gray-700">
                        <option value="stripe" {{ $booking->booking_payment_gateway == "stripe" ? 'selected' : 'hidden' }}>{{t('flightinvoice.stripe')}}</option>
                        <option value="payone" {{ $booking->booking_payment_gateway == "payone" ? 'selected' : 'hidden' }}>{{t('flightinvoice.payone')}}</option>
                    </select>

                    <div id="paymentError" class="hidden mt-2 text-xs text-red-600 font-semibold">
                        <i class="fas fa-exclamation-circle"></i> {{t('flightinvoice.paymentError')}}
                    </div>
                </div>
                @else
                    <div>
                        <h3 class="text-xs font-bold text-blue-900 uppercase tracking-wider mb-2 pb-1 border-b border-gray-200"></h3>
                    </div>
                @endif

                <!-- Price Summary -->
                <div>
                    <h3 class="text-xs font-bold text-blue-900 uppercase tracking-wider mb-2 pb-1 border-b border-gray-200">{{t('flightinvoice.priceSummary')}}</h3>
                    <div class="bg-blue-50 border-2 border-blue-600 rounded-lg p-3" style="border-color: #0077BE;">
                        <div class="flex justify-between items-center text-xs mb-2 pb-2 border-b border-gray-300">
                            <span class="text-gray-700 font-medium">{{t('flightinvoice.flightCharges')}}</span>
                            <span class="font-semibold text-gray-800">{{$segmentData['segments'][0][0]['currency']}} {{$segmentData['segments'][0][0]['price']}}</span>
                        </div>
                        <div class="flex justify-between items-center text-xs mb-2 pb-2 border-b border-gray-300">
                            <span class="text-gray-700 font-medium">{{t('flightinvoice.taxesFees')}}</span>
                            <span class="font-semibold text-gray-800">{{$segmentData['segments'][0][0]['currency']}} 0.00</span>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t-2 border-blue-600" style="border-color: #0077BE;">
                            <span class="text-sm font-bold text-blue-900">{{t('flightinvoice.total')}}</span>
                            <span class="text-lg font-bold text-blue-900">{{$segmentData['segments'][0][0]['currency']}} {{$segmentData['segments'][0][0]['price']}}</span>
                        </div>
                    </div>
                </div>
            </div>

            @if($booking->booking_status_flag != "confirmed" && $booking->booking_payment_status != "paid"  && $booking->booking_payment_gateway !="after_pay")
                <form method="get" action="{{ route('payment', ['gateway_name' => strtolower($booking->booking_payment_gateway), 'booking_ref' => $booking->booking_code_ref]) }}">
                    <!-- Pay Now Button -->
                    <button onclick="processPayment()" style="background-color: #0077BE;" class="w-full px-4 py-3 text-white font-bold rounded-lg transition shadow-lg flex items-center justify-center gap-2 text-sm hover:opacity-90">
                        <i class="fas fa-lock"></i>
                        {{t('flightinvoice.payNow')}}
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
            @endif

            <div class="mt-2 text-center">
                <span class="inline-flex items-center gap-1.5 text-xs text-green-700 bg-green-100 px-2 py-1 rounded-full font-semibold">
                    <i class="fas fa-shield-alt"></i>
                    {{t('flightinvoice.securePayment')}}
                </span>
            </div>

            <!-- Important Information -->
            <div class="bg-gray-50 border-t border-gray-200 rounded-lg p-3 mt-4">
                <div class="text-xs font-bold text-blue-900 uppercase tracking-wider mb-1">{{t('flightinvoice.importantInfo')}}</div>
                <div class="text-[10px] text-gray-700 leading-relaxed space-y-0.5">
                    <p>• {{t('flightinvoice.info1')}}</p>
                    <p>• {{t('flightinvoice.info2')}}</p>
                    <p>• {{t('flightinvoice.info3')}}</p>
                    <p>• {{t('flightinvoice.info4')}}</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 border-t border-gray-200 p-3 text-center">
            <p class="text-[10px] text-gray-500">© 2025 {{getSetting('business_name', 'main', 'Default Title')}}. All rights reserved. | Computer-generated invoice.</p>
            <p class="text-[10px] text-gray-500">Support: {{getSetting('contact_email', 'contact')}}| {{getSetting('contact_phone', 'contact')}}</p>
        </div>
    </div>
</div>

<script>
    function downloadPDF() {
        alert('PDF download functionality would be implemented');
    }

    function sendEmail() {
        // Show loading state
        const button = event.currentTarget;
        const originalContent = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> {{t("flightinvoice.sending")}}...';

        // Get booking reference from URL or page
        const bookingRef = '{{ $booking->booking_code_ref }}';

        // Get CSRF token
        const csrfToken = '{{ csrf_token() }}';

        // Send AJAX request
        fetch(`/flight/send-invoice-email/${bookingRef}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            button.disabled = false;
            button.innerHTML = originalContent;

            if (data.success) {
                // Show success message
                alert('✅ ' + data.message);
            } else {
                // Show error message
                alert('❌ ' + data.message);
            }
        })
        .catch(error => {
            button.disabled = false;
            button.innerHTML = originalContent;
            console.error('Error:', error);
            alert('❌ {{t("flightinvoice.emailError")}}');
        });
    }

    function processPayment() {
        const paymentMethod = document.getElementById('paymentMethod').value;
        const errorDiv = document.getElementById('paymentError');

        if (!paymentMethod) {
            errorDiv.classList.remove('hidden');
            document.getElementById('paymentMethod').classList.add('border-red-500', 'ring-2', 'ring-red-500');
            return;
        }

        errorDiv.classList.add('hidden');
        document.getElementById('paymentMethod').classList.remove('border-red-500', 'ring-2', 'ring-red-500');

        alert('Processing payment via: ' + paymentMethod.toUpperCase().replace('_', ' ') + '...');
        // Add your payment processing logic here
    }

    // Remove error styling when user selects a payment method
    const paymentMethodSelect = document.getElementById('paymentMethod');
    if (paymentMethodSelect) {
        paymentMethodSelect.addEventListener('change', function() {
            if (this.value) {
                document.getElementById('paymentError').classList.add('hidden');
                this.classList.remove('border-red-500', 'ring-2', 'ring-red-500');
            }
        });
    }
</script>

</body>
</html>
