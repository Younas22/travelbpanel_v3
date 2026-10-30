@extends('common.layout')
@section('page_class', 'view-track-ticket')
@section('content')

<div id="siteRedesign">
    <section class="hero-section is-compact">
        <div class="hero-content">
            <h1 class="hero-title">Track Your Ticket or Booking</h1>
            <p class="hero-subtitle">Enter your booking/invoice reference or support ticket number to view it.</p>
        </div>
    </section>

    <div class="form-container is-overlap pg-panel tk-panel">
        @if(session('error'))
            <div class="fb-alert"><i class="fas fa-circle-exclamation"></i> {{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('ticket.lookup') }}" class="tk-form">
            @csrf
            <label class="fb-field">
                <span>Booking/Invoice Reference or Ticket Number *</span>
                <div class="tk-input-row">
                    <div class="tl-input-icon tk-input">
                        <i class="fas fa-ticket"></i>
                        <input type="text" name="ticket_number" required placeholder="e.g. 20260202025457 or TKT-20261001-04ADC"
                               value="{{ old('ticket_number') }}" class="tl-name-search" autocomplete="off">
                    </div>
                    <button type="submit" class="td-book-btn tk-btn"><i class="fas fa-magnifying-glass"></i> Find It</button>
                </div>
            </label>
            @error('ticket_number')<p class="tk-error">{{ $message }}</p>@enderror
        </form>

        <div class="tk-help">
            <div><span class="td-fact-icon"><i class="fas fa-file-invoice"></i></span><p><b>Booking reference</b>Opens your invoice.</p></div>
            <div><span class="td-fact-icon"><i class="fas fa-headset"></i></span><p><b>Ticket number (TKT-...)</b>Opens your support conversation.</p></div>
            <div><span class="td-fact-icon"><i class="fas fa-user"></i></span><p><b>Have an account?</b>Logged-in customers and agents can also see all of these from their own dashboard.</p></div>
        </div>
    </div>
</div>
@endsection
