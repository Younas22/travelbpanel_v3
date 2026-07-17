@extends('common.layout')
@section('content')


   <!-- Legal Page Header -->
    <section class="legal-hero-section">
        <div class="container">
            <div class="legal-hero-content">
                <div class="legal-breadcrumb">
                    <a href="{{url('home')}}">Home</a>
                    <i class="fas fa-chevron-right"></i>
                    <span>Sitemap</span>
                </div>
                <h1 class="legal-hero-title">Sitemap</h1>
                <!-- <p class="legal-hero-subtitle">Your privacy matters to us. Learn how we collect, use, and protect your personal information.</p> -->
                <div class="legal-meta">
                    <div class="legal-meta-item">
                        <i class="fas fa-calendar"></i>
                        <span>Last Updated: <?= date('F j, Y'); ?></span>
                    </div>
                    <!-- <div class="legal-meta-item">
                        <i class="fas fa-shield-alt"></i>
                        <span>GDPR Compliant</span>
                    </div> -->
                </div>
            </div>
        </div>
    </section>

    <!-- Legal Content -->
    <section class="legal-content-section">
        <div class="container">
                <!-- Main Content -->
                <div class="legal-main">
                    <div class="legal-document">
                        <ul class="space-y-4 text-lg text-gray-700">
                <li>
                    <a href="{{ url('/home') }}" class="hover:text-indigo-600 transition duration-200">🏠 Home</a>
                </li>
                <li>
                    <a href="{{ url('/flight') }}" class="hover:text-indigo-600 transition duration-200">✈️ Flight</a>
                </li>
                <li>
                    <a href="{{ url('/visa') }}" class="hover:text-indigo-600 transition duration-200">🛂 Visa</a>
                </li>
                <li>
                    <a href="{{ route('blog.index') }}" class="hover:text-indigo-600 transition duration-200">📝 Blog</a>
                </li>
                <li>
                    <a href="{{ url('/page/about') }}" class="hover:text-indigo-600 transition duration-200">👤 About</a>
                </li>
                <li>
                    <a href="{{ url('/page/contact') }}" class="hover:text-indigo-600 transition duration-200">📞 Contact</a>
                </li>
                <li>
                    <a href="{{ url('/page/sitemap') }}" class="hover:text-indigo-600 transition duration-200">🗺️ Sitemap</a>
                </li>
                <li>
                    <a href="{{ url('/page/privacy-policy') }}" class="hover:text-indigo-600 transition duration-200">🔐 Privacy Policy</a>
                </li>
                <li>
                    <a href="{{ url('/page/terms-conditions') }}" class="hover:text-indigo-600 transition duration-200">📄 Terms & Conditions</a>
                </li>
            </ul>
                    </div>
                </div>
        </div>
    </section>
 

@endsection
