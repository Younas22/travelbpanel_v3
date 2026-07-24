{{-- Disclosure: mirrors agent/flights/index.blade.php. The entire agent flight-
     booking feature is dead/unreachable code: Agent\FlightController exists and
     calls route('agent.flights.*') internally, but no route in routes/agent.php
     or routes/web.php ever registers those names, so this controller/view
     family can never actually be reached by a request. Kept for file-tree
     parity with the exhaustive file list this task specifies. --}}
@extends('agent-modern.layouts.app')
@section('title', 'Flights')

@section('content')
<div class="flex justify-between items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-airplane"></i> Flight Search</h4>
    <a href="{{ route('agent.wallet.index') }}" class="ap-badge ap-badge-success">
        <i class="bi bi-wallet2"></i> Balance: <strong>{{ activeCurrency()->currency_name }} {{ number_format(auth()->user()->wallet?->balance ?? 0, 0) }}</strong>
    </a>
</div>

<div class="ap-card">
    <div class="ap-card-body">
        <div class="mb-3">
            <div class="inline-flex rounded-lg overflow-hidden border border-gray-200" id="tripToggle">
                <button type="button" class="ap-tab-pill-active px-3 py-1.5 text-sm font-medium" data-trip="oneway">One Way</button>
                <button type="button" class="ap-tab-pill-inactive px-3 py-1.5 text-sm font-medium" data-trip="round">Round Trip</button>
            </div>
        </div>
        <form method="GET" action="{{ route('agent.flights.search') }}">
            <input type="hidden" name="trip" id="tripInput" value="oneway">
            <div class="grid grid-cols-2 md:grid-cols-12 gap-3">
                <div class="md:col-span-2">
                    <label class="form-label">From</label>
                    <input type="text" name="origin" class="form-control" placeholder="City or Airport" required>
                </div>
                <div class="md:col-span-2">
                    <label class="form-label">To</label>
                    <input type="text" name="destination" class="form-control" placeholder="City or Airport" required>
                </div>
                <div class="md:col-span-2">
                    <label class="form-label">Departure</label>
                    <input type="date" name="departure_date" class="form-control" min="{{ date('Y-m-d') }}" required>
                </div>
                <div class="md:col-span-2 hidden" id="returnDateGroup">
                    <label class="form-label">Return</label>
                    <input type="date" name="return_date" class="form-control">
                </div>
                <div class="md:col-span-1">
                    <label class="form-label">Class</label>
                    <select name="flight_type" class="form-select">
                        <option value="economy">Economy</option>
                        <option value="business">Business</option>
                        <option value="first">First</option>
                    </select>
                </div>
                <div class="md:col-span-1">
                    <label class="form-label">Adults</label>
                    <select name="adult" class="form-select">
                        @for($i=1;$i<=6;$i++)<option value="{{ $i }}">{{ $i }}</option>@endfor
                    </select>
                </div>
                <div class="md:col-span-1">
                    <label class="form-label">Children</label>
                    <select name="child" class="form-select">
                        @for($i=0;$i<=4;$i++)<option value="{{ $i }}">{{ $i }}</option>@endfor
                    </select>
                </div>
                <div class="md:col-span-1 flex items-end">
                    <button type="submit" class="ap-btn-primary w-full justify-center">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('#tripToggle button').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('#tripToggle button').forEach(b => {
            b.classList.remove('ap-tab-pill-active');
            b.classList.add('ap-tab-pill-inactive');
        });
        this.classList.add('ap-tab-pill-active');
        this.classList.remove('ap-tab-pill-inactive');
        const trip = this.dataset.trip;
        document.getElementById('tripInput').value = trip;
        document.getElementById('returnDateGroup').classList.toggle('hidden', trip !== 'round');
    });
});
</script>
@endpush
