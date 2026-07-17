<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Booking Invoice</title>
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
            <i class="fas fa-print"></i> {{t('hotelinvoice.print')}}
        </button>
        <button onclick="downloadPDF()" class="px-3 py-2 border border-gray-300 bg-white text-gray-800 rounded-lg font-semibold text-xs hover:border-blue-600 hover:bg-blue-50 hover:text-blue-600 transition flex items-center gap-1.5">
            <i class="fas fa-download"></i> {{t('hotelinvoice.downloadPdf')}}
        </button>
        <button onclick="sendEmail()" class="px-3 py-2 text-white rounded-lg font-semibold text-xs transition shadow-lg flex items-center gap-1.5 btn-primary">
            <i class="fas fa-envelope"></i> {{t('hotelinvoice.sendEmail')}}
        </button>
    </div>

    @php
        // Decode booking_data if it's a string, otherwise use as-is
        if (is_string($booking->booking_data)) {
            $data = json_decode($booking->booking_data);
        } else {
            $data = json_decode(json_encode($booking->booking_data));
        }

        // Calculate days between check-in and check-out
        $bookingData = is_object($data->booking_data) ? $data->booking_data : (object)$data->booking_data;
        $days = (new DateTime($bookingData->checkin))->diff(new DateTime($bookingData->checkout))->days;

        // Get guest data from booking_guest field
        $guestData = [];
        if (!empty($booking->booking_guest)) {
            if (is_string($booking->booking_guest)) {
                $guestData = json_decode($booking->booking_guest, true);
            } else {
                $guestData = $booking->booking_guest;
            }
        }
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
                    <p class="text-xs text-gray-600 uppercase">{{t('hotelinvoice.invoice')}}</p>
                    <strong class="text-base font-bold text-gray-800 block">#INV-{{ $booking->booking_code_ref }}</strong>
                    <p class="text-xs text-gray-600">{{ $booking->created_at->format('d M Y') }}</p>
                </div>
            </div>

            <!-- Status Badges -->
            <div class="flex items-center justify-between pt-3 border-t border-gray-200">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-gray-700">{{t('hotelinvoice.bookingStatus')}}:</span>
                    @if($booking->booking_status_flag == 'confirmed')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-green-100 text-green-800 border border-green-300 rounded font-bold text-xs">
                        <i class="fas fa-check-circle"></i>
                        {{t('hotelinvoice.confirmed')}}
                    </span>
                    @elseif($booking->booking_status_flag == 'cancelled')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-100 text-red-800 border border-red-300 rounded font-bold text-xs">
                        <i class="fas fa-times-circle"></i>
                        {{t('hotelinvoice.cancelled')}}
                    </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-yellow-100 text-yellow-800 border border-yellow-300 rounded font-bold text-xs">
                        <i class="fas fa-clock"></i>
                        {{t('hotelinvoice.pending')}}
                    </span>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-gray-700">{{t('hotelinvoice.paymentStatus')}}:</span>
                    @if($booking->booking_payment_state == 'paid')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-green-100 text-green-800 border border-green-300 rounded font-bold text-xs">
                        <i class="fas fa-check-circle"></i>
                        {{t('hotelinvoice.paid')}}
                    </span>
                    @elseif($booking->booking_payment_state == 'refunded')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-100 text-blue-800 border border-blue-300 rounded font-bold text-xs">
                        <i class="fas fa-undo"></i>
                        {{t('hotelinvoice.refunded')}}
                    </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-yellow-100 text-yellow-800 border border-yellow-300 rounded font-bold text-xs">
                        <i class="fas fa-clock"></i>
                        {{t('hotelinvoice.pending')}}
                    </span>
                    @endif
                </div>
            </div>
        </div>

@if(empty( $booking->booking_pnr) && $booking->booking_payment_state == 'paid')

        <div class="bg-red-50 border-l-4 border-red-400 p-4 mt-4">
            <div class="flex items-start gap-2">
                <i class="fas fa-exclamation-triangle text-yellow-600 mt-0.5"></i>
                <div class="text-xs text-red-800">
                    {{$booking->booking_response_error}}
                </div>
            </div>
        </div>
        @endif

        <!-- Body -->
        <div class="p-5">

            <!-- Hotel Information -->
            <div class="mb-4">
                <h3 class="text-xs font-bold text-blue-900 uppercase tracking-wider mb-2 pb-1 border-b border-gray-200">{{t('hotelinvoice.hotelInformation')}}</h3>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                    <h4 class="font-bold text-gray-900 text-sm mb-1">{{$bookingData->hotel_name ?? 'N/A'}}</h4>
                    <p class="text-xs text-gray-600 flex items-center gap-1 mb-1">
                        <i class="fas fa-map-marker-alt text-blue-600"></i>
                        {{$bookingData->address ?? 'N/A'}}
                    </p>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="text-yellow-500 text-sm">{!! str_repeat('★', $bookingData->stars ?? 5) !!}</span>
                        <span class="text-xs text-gray-600">{{$bookingData->stars ?? 5}} {{t('hotelinvoice.starHotel')}}</span>
                    </div>
                </div>
            </div>

            <!-- Booking Details -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-3 mb-4">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                    <div>
                        <div class="text-gray-600 font-semibold mb-0.5">{{t('hotelinvoice.bookingRef')}}</div>
                        <div class="font-bold text-gray-800">{{$booking->booking_code_ref}}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 font-semibold mb-0.5">{{t('hotelinvoice.bookingPnr')}}</div>
                        <div class="font-bold text-gray-800">{{ $booking->booking_pnr }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 font-semibold mb-0.5">{{t('hotelinvoice.checkIn')}}</div>
                        <div class="font-bold text-gray-800">{{\Carbon\Carbon::parse($bookingData->checkin)->format('d M Y')}}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 font-semibold mb-0.5">{{t('hotelinvoice.checkOut')}}</div>
                        <div class="font-bold text-gray-800">{{\Carbon\Carbon::parse($bookingData->checkout)->format('d M Y')}}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 font-semibold mb-0.5">{{t('hotelinvoice.nights')}}</div>
                        <div class="font-bold text-gray-800">{{$days}}</div>
                    </div>
                </div>
            </div>

            <!-- Room Details -->
            <div class="mb-4">
                <h3 class="text-xs font-bold text-blue-900 uppercase tracking-wider mb-2 pb-1 border-b border-gray-200">{{t('hotelinvoice.roomDetails')}}</h3>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                    <div class="flex justify-between items-start">
                        <div>
                            <h4 class="font-bold text-gray-900 text-sm mb-1">{{$data->room->name}}</h4>
                            <p class="text-xs text-gray-600">{{$days}} {{t('hotelinvoice.nights')}}</p>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-bold text-blue-900">{{$data->room->price}} {{$booking->booking_currency_origin}}</div>
                            <div class="text-xs text-gray-600">{{t('hotelinvoice.perStay')}}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Guest Details Table -->
            <div class="mb-4">
                <h3 class="text-xs font-bold text-blue-900 uppercase tracking-wider mb-2 pb-1 border-b border-gray-200">{{t('hotelinvoice.guestInformation')}}</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-[10px] border-collapse">
                        <thead>
                            <tr class="bg-blue-50 border border-gray-300">
                                <th class="border border-gray-300 px-2 py-1.5 text-left font-bold text-blue-900">{{t('hotelinvoice.no')}}</th>
                                <th class="border border-gray-300 px-2 py-1.5 text-left font-bold text-blue-900">{{t('hotelinvoice.guestType')}}</th>
                                <th class="border border-gray-300 px-2 py-1.5 text-left font-bold text-blue-900">{{t('hotelinvoice.name')}}</th>
                                <th class="border border-gray-300 px-2 py-1.5 text-left font-bold text-blue-900">{{t('hotelinvoice.gender')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(!empty($guestData) && is_array($guestData))
                                @foreach($guestData as $index => $guest)
                            <tr class="border border-gray-300 hover:bg-gray-50">
                                <td class="border border-gray-300 px-2 py-1.5 font-semibold">{{ $index + 1 }}</td>
                                <td class="border border-gray-300 px-2 py-1.5">
                                    @if(isset($guest['traveller_type']) && $guest['traveller_type'] === 'child')
                                        <span class="inline-block px-2 py-0.5 bg-green-100 text-green-800 rounded text-[9px] font-semibold">{{t('hotelinvoice.child')}}</span>
                                    @else
                                        <span class="inline-block px-2 py-0.5 bg-blue-100 text-blue-800 rounded text-[9px] font-semibold">{{t('hotelinvoice.adult')}}</span>
                                    @endif
                                </td>
                                <td class="border border-gray-300 px-2 py-1.5 font-semibold">
                                    {{ $guest['first_name'] ?? 'N/A' }} {{ $guest['last_name'] ?? 'N/A' }}
                                </td>
                                <td class="border border-gray-300 px-2 py-1.5">
                                    {{ ucfirst($guest['title'] ?? 'N/A') }}
                                </td>
                            </tr>
                                @endforeach
                            @else
                            <!-- Sample Data -->
                            <tr class="border border-gray-300 hover:bg-gray-50">
                                <td class="border border-gray-300 px-2 py-1.5 font-semibold">1</td>
                                <td class="border border-gray-300 px-2 py-1.5">
                                    <span class="inline-block px-2 py-0.5 bg-blue-100 text-blue-800 rounded text-[9px] font-semibold">{{t('hotelinvoice.adult')}}</span>
                                </td>
                                <td class="border border-gray-300 px-2 py-1.5 font-semibold">Ahmed Khan</td>
                                <td class="border border-gray-300 px-2 py-1.5">Male</td>
                            </tr>
                            <tr class="border border-gray-300 hover:bg-gray-50">
                                <td class="border border-gray-300 px-2 py-1.5 font-semibold">2</td>
                                <td class="border border-gray-300 px-2 py-1.5">
                                    <span class="inline-block px-2 py-0.5 bg-blue-100 text-blue-800 rounded text-[9px] font-semibold">{{t('hotelinvoice.adult')}}</span>
                                </td>
                                <td class="border border-gray-300 px-2 py-1.5 font-semibold">Sarah Khan</td>
                                <td class="border border-gray-300 px-2 py-1.5">Female</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Payment Method & Price Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
                <!-- Payment Method Dropdown -->
                @if($booking->booking_status_flag != "confirmed" && $booking->booking_payment_state != "paid" && $booking->booking_payment_gateway !="after_pay")
                    <!-- Payment Method Dropdown -->
                    <div>
                        <h3 class="text-xs font-bold text-blue-900 uppercase tracking-wider mb-2 pb-1 border-b border-gray-200">{{t('hotelinvoice.selectPaymentMethod')}}</h3>
                        <select id="paymentMethod" class="w-full px-3 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm font-medium text-gray-700">
                            <option value="stripe" {{ $booking->booking_payment_gateway == "stripe" ? 'selected' : 'hidden' }}>{{t('hotelinvoice.stripe')}}</option>
                            <option value="payone" {{ $booking->booking_payment_gateway == "payone" ? 'selected' : 'hidden' }}>{{t('hotelinvoice.payone')}}</option>
                            <option value="payone" {{ $booking->booking_payment_gateway == "paypal" ? 'selected' : 'hidden' }}>{{t('hotelinvoice.paypal')}}</option>
                        </select>

                        <div id="paymentError" class="hidden mt-2 text-xs text-red-600 font-semibold">
                            <i class="fas fa-exclamation-circle"></i> {{t('hotelinvoice.paymentError')}}
                        </div>
                    </div>
                @else
                    <div>
                        <h3 class="text-xs font-bold text-blue-900 uppercase tracking-wider mb-2 pb-1 border-b border-gray-200"></h3>
                    </div>
                @endif

                <!-- Price Summary -->
                <div>
                    <h3 class="text-xs font-bold text-blue-900 uppercase tracking-wider mb-2 pb-1 border-b border-gray-200">{{t('hotelinvoice.priceSummary')}}</h3>
                    <div class="bg-blue-50 border-2 border-blue-600 rounded-lg p-3" style="border-color: #0077BE;">
                        <div class="flex justify-between items-center text-xs mb-2 pb-2 border-b border-gray-300">
                            <span class="text-gray-700 font-medium">{{t('hotelinvoice.roomCharges')}} ({{$days}} {{t('hotelinvoice.nights')}})</span>
                            <span class="font-semibold text-gray-800">{{$data->room->price}} {{$booking->booking_currency_origin}}</span>
                        </div>
                        <div class="flex justify-between items-center text-xs mb-2 pb-2 border-b border-gray-300">
                            <span class="text-gray-700 font-medium">{{t('hotelinvoice.taxesFees')}}</span>
                            <span class="font-semibold text-gray-800">{{$booking->booking_currency_origin}} 0.00</span>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t-2 border-blue-600" style="border-color: #0077BE;">
                            <span class="text-sm font-bold text-blue-900">{{t('hotelinvoice.totalAmount')}}</span>
                            <span class="text-lg font-bold text-blue-900">{{$data->room->price}} {{$booking->booking_currency_origin}}</span>
                        </div>
                    </div>
                </div>
            </div>

            @if($booking->booking_status_flag != "confirmed" && $booking->booking_payment_state != "paid"  && $booking->booking_payment_gateway !="after_pay")
                <form method="get" action="{{ route('payment.hotel', ['gateway_name' => strtolower($booking->booking_payment_gateway), 'booking_ref' => $booking->booking_code_ref]) }}">
                    <!-- Pay Now Button -->
                    <button onclick="processPayment()" style="background-color: #0077BE;" class="w-full px-4 py-3 text-white font-bold rounded-lg transition shadow-lg flex items-center justify-center gap-2 text-sm hover:opacity-90">
                        <i class="fas fa-lock"></i>
                        {{t('hotelinvoice.payNow')}}
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
            @endif
            <div class="mt-2 text-center print:hidden">
                <span class="inline-flex items-center gap-1.5 text-xs text-green-700 bg-green-100 px-2 py-1 rounded-full font-semibold">
                    <i class="fas fa-shield-alt"></i>
                    {{t('hotelinvoice.securePayment')}}
                </span>
            </div>


            <!-- Hotel Policies -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 mb-4 mt-4">
                <div class="text-xs font-bold text-blue-900 uppercase tracking-wider mb-2">{{t('hotelinvoice.hotelPolicies')}}</div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-[10px] text-gray-700">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-clock text-blue-600 mt-0.5"></i>
                        <div>
                            <strong>{{t('hotelinvoice.checkIn')}}:</strong> 2:00 PM<br>
                            <strong>{{t('hotelinvoice.checkOut')}}:</strong> 11:00 AM
                        </div>
                    </div>
                    <div class="flex items-start gap-2">
                        <i class="fas fa-times-circle text-red-600 mt-0.5"></i>
                        <div>
                            <strong>{{t('hotelinvoice.cancellation')}}:</strong> {{t('hotelinvoice.cancellationPolicy')}}
                        </div>
                    </div>
                    <div class="flex items-start gap-2">
                        <i class="fas fa-credit-card text-green-600 mt-0.5"></i>
                        <div>
                            <strong>{{t('hotelinvoice.payment')}}:</strong> {{t('hotelinvoice.paymentPolicy')}}
                        </div>
                    </div>
                    <div class="flex items-start gap-2">
                        <i class="fas fa-concierge-bell text-blue-600 mt-0.5"></i>
                        <div>
                            <strong>{{t('hotelinvoice.services')}}:</strong> {{t('hotelinvoice.servicesPolicy')}}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Important Information -->
            <div class="bg-gray-50 border-t border-gray-200 rounded-lg p-3">
                <div class="text-xs font-bold text-blue-900 uppercase tracking-wider mb-1">{{t('hotelinvoice.importantInfo')}}</div>
                <div class="text-[10px] text-gray-700 leading-relaxed space-y-0.5">
                    <p>• {{t('hotelinvoice.info1')}}</p>
                    <p>• {{t('hotelinvoice.info2')}}</p>
                    <p>• {{t('hotelinvoice.info3')}}</p>
                    <p>• {{t('hotelinvoice.info4')}}</p>
                    <p>• {{t('hotelinvoice.info5')}}</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 border-t border-gray-200 p-3 text-center">
            <p class="text-[10px] text-gray-500">© 2025 {{t('hotelinvoice.footer')}}</p>
            <p class="text-[10px] text-gray-500">{{t('hotelinvoice.support')}}: support@travelbooox.com | +92 (21) 1234-5678</p>
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
        // Add your payment processing logic here
    }

    // Remove error styling when user selects a payment method
    document.getElementById('paymentMethod').addEventListener('change', function() {
        if (this.value) {
            document.getElementById('paymentError').classList.add('hidden');
            this.classList.remove('border-red-500', 'ring-2', 'ring-red-500');
        }
    });
</script>

</body>
</html>
