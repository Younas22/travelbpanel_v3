@php
    $currentLocale = app()->getLocale();
    $errorTrans = json_decode(file_get_contents(resource_path("lang/{$currentLocale}/error.json")), true);
    $isDemoSite = request()->getSchemeAndHttpHost() === 'https://demo.travelbookingpanel.com';
@endphp
<!DOCTYPE html>
<html lang="{{ $currentLocale }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $errorTrans['page_title'] ?? 'Page Not Found' }} - TravelBooking Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            DEFAULT: '#0077BE',
                            dark: '#005a91',
                            light: '#e6f2fa',
                            50: '#f0f8ff',
                            100: '#dceefb',
                            200: '#b8ddf7',
                            600: '#0077BE',
                            700: '#005a91',
                            800: '#004570',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; }

        .float-animation {
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .pulse-ring {
            animation: pulseRing 2s ease-out infinite;
        }
        @keyframes pulseRing {
            0% { transform: scale(0.8); opacity: 1; }
            100% { transform: scale(1.4); opacity: 0; }
        }

        .gradient-text {
            background: linear-gradient(135deg, #0077BE, #00b4d8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 119, 190, 0.15);
        }

        .bg-pattern {
            background-image: radial-gradient(circle at 20% 50%, rgba(0, 119, 190, 0.05) 0%, transparent 50%),
                              radial-gradient(circle at 80% 20%, rgba(0, 180, 216, 0.05) 0%, transparent 50%),
                              radial-gradient(circle at 50% 80%, rgba(0, 119, 190, 0.03) 0%, transparent 50%);
        }
    </style>
</head>
<body class="bg-gray-50 bg-pattern min-h-screen flex items-center justify-center p-4">

    <div class="max-w-3xl w-full text-center">

        <!-- Animated 404 Illustration -->
        <div class="relative mb-8">
            <div class="float-animation inline-block relative">
                <!-- Globe/Travel Icon -->
                <div class="relative">
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="w-40 h-40 rounded-full bg-brand-100 pulse-ring"></div>
                    </div>
                    <svg class="w-40 h-40 mx-auto relative z-10" viewBox="0 0 200 200" fill="none">
                        <circle cx="100" cy="100" r="80" fill="#e6f2fa" stroke="#0077BE" stroke-width="3"/>
                        <ellipse cx="100" cy="100" rx="40" ry="80" fill="none" stroke="#0077BE" stroke-width="2" opacity="0.5"/>
                        <line x1="20" y1="100" x2="180" y2="100" stroke="#0077BE" stroke-width="2" opacity="0.5"/>
                        <line x1="100" y1="20" x2="100" y2="180" stroke="#0077BE" stroke-width="2" opacity="0.3"/>
                        <ellipse cx="100" cy="70" rx="60" ry="15" fill="none" stroke="#0077BE" stroke-width="1.5" opacity="0.3"/>
                        <ellipse cx="100" cy="130" rx="60" ry="15" fill="none" stroke="#0077BE" stroke-width="1.5" opacity="0.3"/>
                        <!-- Airplane -->
                        <g transform="translate(55, 45) rotate(-20)">
                            <path d="M0 15 L30 5 L60 0 L30 10 L70 25 L30 15 L60 30 L30 20 L0 15Z" fill="#0077BE" opacity="0.8"/>
                        </g>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Error Code -->
        <h1 class="text-8xl md:text-9xl font-black gradient-text mb-2 leading-none">{{ $errorTrans['error_code'] ?? '404' }}</h1>

        <!-- Error Title -->
        <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-3">
            {{ $errorTrans['error_title'] ?? 'Oops! This Page Has Taken Off' }}
        </h2>
        <p class="text-gray-500 text-lg mb-10 max-w-md mx-auto">
            {{ $errorTrans['error_description'] ?? "The page you're looking for doesn't exist or has been moved to a new destination." }}
        </p>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 {{ $isDemoSite ? 'mb-14' : 'mb-10' }}">
            <a href="{{ url('/') }}"
               class="inline-flex items-center gap-2 bg-brand text-white font-semibold px-8 py-3.5 rounded-xl shadow-lg shadow-brand/30 hover:bg-brand-dark transition-all duration-300 hover:shadow-xl hover:shadow-brand/40 hover:-translate-y-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1"/>
                </svg>
                {{ $errorTrans['go_home'] ?? 'Go to Home Page' }}
            </a>
            @if($isDemoSite)
            <a href="https://travelbookingpanel.com/pricing" target="_blank"
               class="inline-flex items-center gap-2 bg-white text-brand font-semibold px-8 py-3.5 rounded-xl border-2 border-brand/20 hover:border-brand hover:bg-brand-50 transition-all duration-300 hover:-translate-y-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ $errorTrans['view_pricing'] ?? 'View Pricing Plans' }}
            </a>
            @endif
        </div>

        @if($isDemoSite)
        <!-- Promo Card -->
        <div class="card-hover bg-white rounded-2xl border border-brand/10 shadow-lg overflow-hidden max-w-xl mx-auto">
            <!-- Top Accent -->
            <div class="h-1.5 bg-gradient-to-r from-brand via-sky-400 to-brand"></div>

            <div class="p-8">
                <div class="inline-flex items-center gap-2 bg-green-50 text-green-700 text-sm font-semibold px-4 py-1.5 rounded-full mb-5">
                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                    {{ $errorTrans['limited_offer'] ?? 'Limited Time Offer' }}
                </div>

                <h3 class="text-xl md:text-2xl font-bold text-gray-800 mb-3">
                    {{ $errorTrans['promo_title'] ?? 'Start Your Travel Business Online' }}
                </h3>

                <p class="text-gray-600 mb-3 leading-relaxed">
                    {!! $errorTrans['promo_description'] ?? 'Launch with style &mdash; <span class="font-semibold text-gray-800">Hotel, Flight, Tour, Visa & Umrah</span> booking all in one platform' !!}
                </p>

                <!-- White Label & Setup Badges -->
                <div class="flex items-center justify-center gap-3 flex-wrap mb-5">
                    <span class="inline-flex items-center gap-1.5 bg-brand-50 text-brand-700 text-xs font-bold px-3 py-1.5 rounded-full border border-brand/10">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/>
                        </svg>
                        {{ $errorTrans['white_label'] ?? 'White Label Solution' }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-700 text-xs font-bold px-3 py-1.5 rounded-full border border-amber-200">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd"/>
                        </svg>
                        {{ $errorTrans['setup_time'] ?? 'Setup in 15 Minutes' }}
                    </span>
                </div>

                <!-- Services Grid -->
                <div class="grid grid-cols-3 sm:grid-cols-5 gap-3 mb-6">
                    <div class="flex flex-col items-center gap-1.5 p-3 rounded-xl bg-brand-50">
                        <svg class="w-7 h-7 text-brand" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5M3.75 21V7.5L12 3l8.25 4.5V21M8.25 21V10.5h7.5V21M6 10.5h.008M6 13.5h.008M6 16.5h.008M18 10.5h.008M18 13.5h.008M18 16.5h.008"/>
                        </svg>
                        <span class="text-xs font-semibold text-brand-700">{{ $errorTrans['hotels'] ?? 'Hotels' }}</span>
                    </div>
                    <div class="flex flex-col items-center gap-1.5 p-3 rounded-xl bg-brand-50">
                        <svg class="w-7 h-7 text-brand" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                        </svg>
                        <span class="text-xs font-semibold text-brand-700">{{ $errorTrans['flights'] ?? 'Flights' }}</span>
                    </div>
                    <div class="flex flex-col items-center gap-1.5 p-3 rounded-xl bg-brand-50">
                        <svg class="w-7 h-7 text-brand" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z"/>
                        </svg>
                        <span class="text-xs font-semibold text-brand-700">{{ $errorTrans['tours'] ?? 'Tours' }}</span>
                    </div>
                    <div class="flex flex-col items-center gap-1.5 p-3 rounded-xl bg-brand-50">
                        <svg class="w-7 h-7 text-brand" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z"/>
                        </svg>
                        <span class="text-xs font-semibold text-brand-700">{{ $errorTrans['visa'] ?? 'Visa' }}</span>
                    </div>
                    <div class="flex flex-col items-center gap-1.5 p-3 rounded-xl bg-brand-50">
                        <svg class="w-7 h-7 text-brand" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12.75 3.03v.568c0 .334.148.65.405.864l1.068.89c.442.369.535 1.01.216 1.49l-.51.766a2.25 2.25 0 01-1.161.886l-.143.048a1.107 1.107 0 00-.57 1.664c.369.555.169 1.307-.427 1.605L9 13.125l.423 1.059a.956.956 0 01-1.652.928l-.679-.906a1.125 1.125 0 00-1.906.172L4.5 15.75l-.612.153M12.75 3.031a9 9 0 00-8.862 12.872M12.75 3.031a9 9 0 016.69 14.036m0 0l-.177-.529A2.25 2.25 0 0017.128 15H16.5l-.324-.324a1.453 1.453 0 00-2.328.377l-.036.073a1.586 1.586 0 01-.982.816l-.99.282c-.55.157-.894.702-.8 1.267l.073.438c.08.474.49.821.97.821.846 0 1.598.542 1.865 1.345l.215.643m5.276-3.67a9.012 9.012 0 01-5.276 3.67m0 0a9 9 0 01-10.275-4.835M15.75 9c0 .896-.393 1.7-1.016 2.25"/>
                        </svg>
                        <span class="text-xs font-semibold text-brand-700">{{ $errorTrans['umrah'] ?? 'Umrah' }}</span>
                    </div>
                </div>

                <!-- Price Tag -->
                <div class="bg-gradient-to-r from-brand to-sky-500 rounded-xl p-5 text-white">
                    <div class="flex items-center justify-center gap-3 flex-wrap">
                        <span class="text-white/80 text-sm font-medium">{{ $errorTrans['starting_from'] ?? 'Starting from just' }}</span>
                        <span class="text-4xl font-black tracking-tight">$1,000</span>
                    </div>
                    <p class="text-white/80 text-sm mt-1">{{ $errorTrans['solution_desc'] ?? 'Complete white-label travel business solution' }}</p>
                </div>

                <!-- CTA -->
                <a href="https://travelbookingpanel.com/pricing" target="_blank"
                   class="mt-6 inline-flex items-center gap-2 bg-brand text-white font-bold px-8 py-3.5 rounded-xl shadow-lg shadow-brand/30 hover:bg-brand-dark transition-all duration-300 hover:shadow-xl hover:-translate-y-0.5">
                    {{ $errorTrans['get_started'] ?? 'Get Started Now' }}
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-10 text-gray-400 text-sm">
            {{ $errorTrans['powered_by'] ?? 'Powered by' }}
            <a href="https://travelbookingpanel.com/pricing" target="_blank" class="text-brand font-semibold hover:underline">
                TravelBooking Panel
            </a>
        </div>
        @endif

    </div>

</body>
</html>
