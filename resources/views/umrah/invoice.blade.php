<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{t('umrahinvoice.title')}}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .btn-primary {
            background-color: #0077BE;
        }
        .btn-primary:hover {
            background-color: #005A9C;
        }
    </style>
</head>
<body class="bg-gray-50">

<div class="max-w-4xl mx-auto p-4">
    <!-- Action Buttons -->
    <div class="flex gap-2 mb-4 justify-end print:hidden">
        <button onclick="window.print()" class="px-3 py-2 border border-gray-300 bg-white text-gray-800 rounded-lg font-semibold text-xs hover:border-blue-600 hover:bg-blue-50 hover:text-blue-600 transition flex items-center gap-1.5">
            <i class="fas fa-print"></i> {{t('umrahinvoice.print')}}
        </button>
        <button onclick="downloadPDF()" class="px-3 py-2 border border-gray-300 bg-white text-gray-800 rounded-lg font-semibold text-xs hover:border-blue-600 hover:bg-blue-50 hover:text-blue-600 transition flex items-center gap-1.5">
            <i class="fas fa-download"></i> {{t('umrahinvoice.downloadPdf')}}
        </button>
        <button onclick="sendEmail()" class="px-3 py-2 text-white rounded-lg font-semibold text-xs transition shadow-lg flex items-center gap-1.5 btn-primary">
            <i class="fas fa-envelope"></i> {{t('umrahinvoice.sendEmail')}}
        </button>
    </div>

    @php
        // Get travellers data
        $travellers = [];
        if (!empty($booking->booking_guest)) {
            if (is_string($booking->booking_guest)) {
                $travellers = json_decode($booking->booking_guest, true);
            } else {
                $travellers = $booking->booking_guest;
            }
        }

        // Get search params
        $searchParams = [];
        if (!empty($booking->search_params)) {
            if (is_string($booking->search_params)) {
                $searchParams = json_decode($booking->search_params, true);
            } else {
                $searchParams = $booking->search_params;
            }
        }
    @endphp

    <!-- Invoice -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-white border-b-4 border-blue-600 p-5" style="border-color: #0077BE;">
            <div class="flex justify-between items-start mb-4">
                <div class="flex items-center gap-3">
                    <img src="{{ getSettingImage('business_logo','branding') }}"
                         alt="Logo"
                         class="h-16 w-auto object-contain">
                </div>

                <div class="text-right">
                    <p class="text-xs text-gray-600 uppercase">{{t('umrahinvoice.invoice')}}</p>
                    <strong class="text-base font-bold text-gray-800 block">#{{ $booking->booking_code_ref }}</strong>
                    <p class="text-xs text-gray-600">{{ \Carbon\Carbon::parse($booking->created_at)->format('d M Y') }}</p>
                </div>
            </div>

            <!-- Status Badges -->
            <div class="flex items-center justify-between pt-3 border-t border-gray-200">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-gray-700">{{t('umrahinvoice.bookingStatus')}}:</span>
                    @if($booking->booking_status_flag == 'confirmed')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-green-100 text-green-800 border border-green-300 rounded font-bold text-xs">
                        <i class="fas fa-check-circle"></i>
                        {{t('umrahinvoice.confirmed')}}
                    </span>
                    @elseif($booking->booking_status_flag == 'cancelled')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-100 text-red-800 border border-red-300 rounded font-bold text-xs">
                        <i class="fas fa-times-circle"></i>
                        {{t('umrahinvoice.cancelled')}}
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-yellow-100 text-yellow-800 border border-yellow-300 rounded font-bold text-xs">
                        <i class="fas fa-clock"></i>
                        {{t('umrahinvoice.pending')}}
                    </span>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-gray-700">{{t('umrahinvoice.paymentStatus')}}:</span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-yellow-100 text-yellow-800 border border-yellow-300 rounded font-bold text-xs">
                        <i class="fas fa-clock"></i>
                        {{t('umrahinvoice.pending')}}
                    </span>
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="p-5">

            <!-- Umrah Package Information -->
            <div class="mb-4">
                <h3 class="text-xs font-bold text-blue-900 uppercase tracking-wider mb-2 pb-1 border-b border-gray-200">
                    <i class="fas fa-kaaba mr-1"></i> {{t('umrahinvoice.umrahPackageInfo')}}
                </h3>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                    <h4 class="font-bold text-gray-900 text-sm mb-1">{{ $booking->umrah_name }}</h4>
                    @if($package)
                    <p class="text-xs text-gray-600 flex items-center gap-1 mb-1">
                        <i class="fas fa-map-marker-alt text-blue-600"></i>
                        {{ $package->loaction ?? 'Saudi Arabia' }}
                    </p>
                    @if($package->packageType)
                    <div class="flex items-center gap-2 mt-2">
                        <span class="inline-block px-2 py-0.5 bg-blue-100 text-blue-800 rounded text-[10px] font-semibold">
                            <i class="fas fa-tag mr-1"></i>{{ $package->packageType->packege_type }}
                        </span>
                    </div>
                    @endif
                    @endif
                </div>
            </div>

            <!-- Booking Details -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-3 mb-4">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                    <div>
                        <div class="text-gray-600 font-semibold mb-0.5">{{t('umrahinvoice.bookingRef')}}</div>
                        <div class="font-bold text-gray-800">{{ $booking->booking_code_ref }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 font-semibold mb-0.5">{{t('umrahinvoice.bookingPnr')}}</div>
                        <div class="font-bold text-gray-800">{{ $booking->booking_pnr }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 font-semibold mb-0.5">{{t('umrahinvoice.departureDate')}}</div>
                        <div class="font-bold text-gray-800">{{ $searchParams['departure_date'] ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 font-semibold mb-0.5">{{t('umrahinvoice.returnDate')}}</div>
                        <div class="font-bold text-gray-800">{{ $searchParams['return_date'] ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 font-semibold mb-0.5">{{t('umrahinvoice.route')}}</div>
                        <div class="font-bold text-gray-800">{{ $searchParams['origin'] ?? '' }} - {{ $searchParams['destination'] ?? '' }}</div>
                    </div>
                </div>
            </div>

            <!-- Umrah Package Details -->
            <div class="mb-4">
                <h3 class="text-xs font-bold text-blue-900 uppercase tracking-wider mb-2 pb-1 border-b border-gray-200">{{t('umrahinvoice.packageDetails')}}</h3>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs mb-3">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-kaaba text-blue-600"></i>
                            <div>
                                <div class="text-gray-600 font-semibold">{{t('umrahinvoice.makkah')}}</div>
                                <div class="font-bold text-gray-800">{{ $searchParams['makkah_nights'] ?? 0 }} {{t('umrahinvoice.nights')}}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-mosque text-green-600"></i>
                            <div>
                                <div class="text-gray-600 font-semibold">{{t('umrahinvoice.madina')}}</div>
                                <div class="font-bold text-gray-800">{{ $searchParams['madina_nights'] ?? 0 }} {{t('umrahinvoice.nights')}}</div>
                            </div>
                        </div>
                        @if($package && $package->class)
                        <div class="flex items-center gap-2">
                            <i class="fas fa-plane text-blue-600"></i>
                            <div>
                                <div class="text-gray-600 font-semibold">{{t('umrahinvoice.flightClass')}}</div>
                                <div class="font-bold text-gray-800">{{ $package->class }}</div>
                            </div>
                        </div>
                        @endif
                        <div class="flex items-center gap-2">
                            <i class="fas fa-users text-blue-600"></i>
                            <div>
                                <div class="text-gray-600 font-semibold">{{t('umrahinvoice.passengers')}}</div>
                                <div class="font-bold text-gray-800">
                                    {{ $booking->booking_adult_count }} {{t('umrahinvoice.adults')}}
                                    @if($booking->booking_child_count > 0), {{ $booking->booking_child_count }} {{t('umrahinvoice.children')}} @endif
                                    @if($booking->infant_count > 0), {{ $booking->infant_count }} {{t('umrahinvoice.infants')}} @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Traveller Details Table -->
            <div class="mb-4">
                <h3 class="text-xs font-bold text-blue-900 uppercase tracking-wider mb-2 pb-1 border-b border-gray-200">{{t('umrahinvoice.travellersInfo')}}</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-[10px] border-collapse">
                        <thead>
                            <tr class="bg-blue-50 border border-gray-300">
                                <th class="border border-gray-300 px-2 py-1.5 text-left font-bold text-blue-900">{{t('umrahinvoice.no')}}</th>
                                <th class="border border-gray-300 px-2 py-1.5 text-left font-bold text-blue-900">{{t('umrahinvoice.travellerType')}}</th>
                                <th class="border border-gray-300 px-2 py-1.5 text-left font-bold text-blue-900">{{t('umrahinvoice.name')}}</th>
                                <th class="border border-gray-300 px-2 py-1.5 text-left font-bold text-blue-900">{{t('umrahinvoice.gender')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(!empty($travellers) && is_array($travellers))
                                @foreach($travellers as $index => $traveller)
                            <tr class="border border-gray-300 hover:bg-gray-50">
                                <td class="border border-gray-300 px-2 py-1.5 font-semibold">{{ $index + 1 }}</td>
                                <td class="border border-gray-300 px-2 py-1.5">
                                    @if(isset($traveller['type']) && $traveller['type'] === 'child')
                                        <span class="inline-block px-2 py-0.5 bg-green-100 text-green-800 rounded text-[9px] font-semibold">{{t('umrahinvoice.child')}}</span>
                                    @elseif(isset($traveller['type']) && $traveller['type'] === 'infant')
                                        <span class="inline-block px-2 py-0.5 bg-yellow-100 text-yellow-800 rounded text-[9px] font-semibold">{{t('umrahinvoice.infant')}}</span>
                                    @else
                                        <span class="inline-block px-2 py-0.5 bg-blue-100 text-blue-800 rounded text-[9px] font-semibold">{{t('umrahinvoice.adult')}}</span>
                                    @endif
                                </td>
                                <td class="border border-gray-300 px-2 py-1.5 font-semibold">
                                    {{ $traveller['first_name'] ?? 'N/A' }} {{ $traveller['last_name'] ?? '' }}
                                </td>
                                <td class="border border-gray-300 px-2 py-1.5">
                                    {{ ucfirst($traveller['gender'] ?? 'N/A') }}
                                </td>
                            </tr>
                                @endforeach
                            @else
                            <tr class="border border-gray-300 hover:bg-gray-50">
                                <td colspan="4" class="border border-gray-300 px-2 py-1.5 text-center text-gray-500">{{t('umrahinvoice.noTravellerInfo')}}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="mb-4">
                <h3 class="text-xs font-bold text-blue-900 uppercase tracking-wider mb-2 pb-1 border-b border-gray-200">{{t('umrahinvoice.contactInfo')}}</h3>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                        <div>
                            <div class="text-gray-600 font-semibold mb-0.5">{{t('umrahinvoice.name')}}</div>
                            <div class="font-bold text-gray-800">{{ $booking->user_first_name }} {{ $booking->user_last_name }}</div>
                        </div>
                        <div>
                            <div class="text-gray-600 font-semibold mb-0.5">{{t('umrahinvoice.email')}}</div>
                            <div class="font-bold text-gray-800">{{ $booking->user_email }}</div>
                        </div>
                        <div>
                            <div class="text-gray-600 font-semibold mb-0.5">{{t('umrahinvoice.phone')}}</div>
                            <div class="font-bold text-gray-800">{{ $booking->user_phone }}</div>
                        </div>
                        <div>
                            <div class="text-gray-600 font-semibold mb-0.5">{{t('umrahinvoice.country')}}</div>
                            <div class="font-bold text-gray-800">{{ strtoupper($booking->user_country) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Method & Price Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
                @if($booking->booking_status_flag != "confirmed" && $booking->booking_payment_state != "paid" && $booking->booking_payment_gateway !="after_pay")
                <!-- Payment Method Dropdown -->
                    <div>
                        <h3 class="text-xs font-bold text-blue-900 uppercase tracking-wider mb-2 pb-1 border-b border-gray-200">{{t('umrahinvoice.selectPaymentMethod')}}</h3>
                        <select id="paymentMethod" class="w-full px-3 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm font-medium text-gray-700">
                            <option value="stripe" {{ $booking->booking_payment_gateway == "stripe" ? 'selected' : 'hidden' }}>{{t('umrahinvoice.stripe')}}</option>
                            <option value="payone" {{ $booking->booking_payment_gateway == "payone" ? 'selected' : 'hidden' }}>{{t('umrahinvoice.payone')}}</option>
                        </select>

                        <div id="paymentError" class="hidden mt-2 text-xs text-red-600 font-semibold">
                            <i class="fas fa-exclamation-circle"></i> {{t('umrahinvoice.paymentError')}}
                        </div>
                    </div>
                @else
                    <div>
                        <h3 class="text-xs font-bold text-blue-900 uppercase tracking-wider mb-2 pb-1 border-b border-gray-200"></h3>
                    </div>
                @endif
                <!-- Price Summary -->
                <div>
                    <h3 class="text-xs font-bold text-blue-900 uppercase tracking-wider mb-2 pb-1 border-b border-gray-200">{{t('umrahinvoice.priceSummary')}}</h3>
                    <div class="bg-blue-50 border-2 border-blue-600 rounded-lg p-3" style="border-color: #0077BE;">
                        <div class="flex justify-between items-center text-xs mb-2 pb-2 border-b border-gray-300">
                            <span class="text-gray-700 font-medium">{{t('umrahinvoice.adults')}} ({{ $booking->booking_adult_count }} x {{ $booking->booking_currency_origin }} {{ number_format($booking->adult_price / max($booking->booking_adult_count, 1), 2) }})</span>
                            <span class="font-semibold text-gray-800">{{ $booking->booking_currency_origin }} {{ number_format($booking->adult_price, 2) }}</span>
                        </div>
                        @if($booking->booking_child_count > 0)
                        <div class="flex justify-between items-center text-xs mb-2 pb-2 border-b border-gray-300">
                            <span class="text-gray-700 font-medium">{{t('umrahinvoice.children')}} ({{ $booking->booking_child_count }} x {{ $booking->booking_currency_origin }} {{ number_format($booking->child_price / max($booking->booking_child_count, 1), 2) }})</span>
                            <span class="font-semibold text-gray-800">{{ $booking->booking_currency_origin }} {{ number_format($booking->child_price, 2) }}</span>
                        </div>
                        @endif
                        @if($booking->infant_count > 0)
                        <div class="flex justify-between items-center text-xs mb-2 pb-2 border-b border-gray-300">
                            <span class="text-gray-700 font-medium">{{t('umrahinvoice.infants')}} ({{ $booking->infant_count }} x {{ $booking->booking_currency_origin }} {{ number_format($booking->infant_price / max($booking->infant_count, 1), 2) }})</span>
                            <span class="font-semibold text-gray-800">{{ $booking->booking_currency_origin }} {{ number_format($booking->infant_price, 2) }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between items-center text-xs mb-2 pb-2 border-b border-gray-300">
                            <span class="text-gray-700 font-medium">{{t('umrahinvoice.taxesFees')}}</span>
                            <span class="font-semibold text-gray-800">{{ $booking->booking_currency_origin }} 0.00</span>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t-2 border-blue-600" style="border-color: #0077BE;">
                            <span class="text-sm font-bold text-blue-900">{{t('umrahinvoice.totalAmount')}}</span>
                            <span class="text-lg font-bold text-blue-900">{{ $booking->booking_currency_origin }} {{ number_format($booking->total_price, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pay Now Button -->

            @if($booking->booking_status_flag != "confirmed" && $booking->booking_payment_state != "paid"  && $booking->booking_payment_gateway !="after_pay")
                <form method="get" action="{{ route('payment.umrah', ['gateway_name' => strtolower($booking->booking_payment_gateway), 'booking_ref' => $booking->booking_code_ref]) }}">
                    <!-- Pay Now Button -->
                    <button onclick="processPayment()" style="background-color: #0077BE;" class="w-full px-4 py-3 text-white font-bold rounded-lg transition shadow-lg flex items-center justify-center gap-2 text-sm hover:opacity-90">
                        <i class="fas fa-lock"></i>
                        {{t('umrahinvoice.payNow')}}
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
            @endif


            <div class="mt-2 text-center print:hidden">
                <span class="inline-flex items-center gap-1.5 text-xs text-green-700 bg-green-100 px-2 py-1 rounded-full font-semibold">
                    <i class="fas fa-shield-alt"></i>
                    {{t('umrahinvoice.securePayment')}}
                </span>
            </div>

            <!-- Umrah Policies -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 mb-4 mt-4">
                <div class="text-xs font-bold text-blue-900 uppercase tracking-wider mb-2">{{t('umrahinvoice.umrahPolicies')}}</div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-[10px] text-gray-700">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-calendar-check text-blue-600 mt-0.5"></i>
                        <div>
                            <strong>{{t('umrahinvoice.departure')}}:</strong> {{ $searchParams['departure_date'] ?? 'N/A' }}<br>
                            <strong>{{t('umrahinvoice.return')}}:</strong> {{ $searchParams['return_date'] ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="flex items-start gap-2">
                        <i class="fas fa-times-circle text-red-600 mt-0.5"></i>
                        <div>
                            <strong>{{t('umrahinvoice.cancellation')}}:</strong> {{t('umrahinvoice.cancellationPolicy')}}
                        </div>
                    </div>
                    <div class="flex items-start gap-2">
                        <i class="fas fa-passport text-green-600 mt-0.5"></i>
                        <div>
                            <strong>{{t('umrahinvoice.documents')}}:</strong> {{t('umrahinvoice.documentsRequired')}}
                        </div>
                    </div>
                    <div class="flex items-start gap-2">
                        <i class="fas fa-suitcase text-blue-600 mt-0.5"></i>
                        <div>
                            <strong>{{t('umrahinvoice.inclusions')}}:</strong> {{t('umrahinvoice.inclusionsPolicy')}}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Important Information -->
            <div class="bg-gray-50 border-t border-gray-200 rounded-lg p-3">
                <div class="text-xs font-bold text-blue-900 uppercase tracking-wider mb-1">{{t('umrahinvoice.importantInfo')}}</div>
                <div class="text-[10px] text-gray-700 leading-relaxed space-y-0.5">
                    <p>* {{t('umrahinvoice.info1')}}</p>
                    <p>* {{t('umrahinvoice.info2')}}</p>
                    <p>* {{t('umrahinvoice.info3')}}</p>
                    <p>* {{t('umrahinvoice.info4')}}</p>
                    <p>* {{t('umrahinvoice.info5')}}</p>
                </div>
            </div>
        </div>

         <!-- Footer -->
        <div class="bg-gray-50 border-t border-gray-200 p-3 text-center">
            <p class="text-[10px] text-gray-500">© <?= date('Y') ?> {{getSetting('business_name', 'main', 'Default Title')}}. {{t('umrahinvoice.footer')}}</p>
            <p class="text-[10px] text-gray-500">{{t('umrahinvoice.support')}}: {{getSetting('contact_email', 'contact')}}| {{getSetting('contact_phone', 'contact')}}</p>
        </div>
    </div>
</div>

<script>
    function downloadPDF() {
        alert('PDF download functionality would be implemented');
    }

    function sendEmail() {
        alert('Invoice sent to your email');
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
    }

    document.getElementById('paymentMethod').addEventListener('change', function() {
        if (this.value) {
            document.getElementById('paymentError').classList.add('hidden');
            this.classList.remove('border-red-500', 'ring-2', 'ring-red-500');
        }
    });
</script>

</body>
</html>
