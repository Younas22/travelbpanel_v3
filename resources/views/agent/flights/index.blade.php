@extends('agent.layouts.app')
@section('title', 'Flights')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-airplane"></i> Flight Search</h4>
    <a href="{{ route('agent.wallet.index') }}" class="btn btn-outline-success btn-sm">
        <i class="bi bi-wallet2"></i> Balance: <strong>{{ activeCurrency()->currency_name }} {{ number_format(auth()->user()->wallet?->balance ?? 0, 0) }}</strong>
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="mb-3">
            <div class="btn-group" id="tripToggle">
                <button type="button" class="btn btn-primary btn-sm active" data-trip="oneway">One Way</button>
                <button type="button" class="btn btn-outline-primary btn-sm" data-trip="round">Round Trip</button>
            </div>
        </div>
        <form method="GET" action="{{ route('agent.flights.search') }}">
            <input type="hidden" name="trip" id="tripInput" value="oneway">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label fw-semibold">From</label>
                    <input type="text" name="origin" class="form-control" placeholder="City or Airport" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">To</label>
                    <input type="text" name="destination" class="form-control" placeholder="City or Airport" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Departure</label>
                    <input type="date" name="departure_date" class="form-control" min="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-2 d-none" id="returnDateGroup">
                    <label class="form-label fw-semibold">Return</label>
                    <input type="date" name="return_date" class="form-control">
                </div>
                <div class="col-md-1">
                    <label class="form-label fw-semibold">Class</label>
                    <select name="flight_type" class="form-select">
                        <option value="economy">Economy</option>
                        <option value="business">Business</option>
                        <option value="first">First</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label fw-semibold">Adults</label>
                    <select name="adult" class="form-select">
                        @for($i=1;$i<=6;$i++)<option value="{{ $i }}">{{ $i }}</option>@endfor
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label fw-semibold">Children</label>
                    <select name="child" class="form-select">
                        @for($i=0;$i<=4;$i++)<option value="{{ $i }}">{{ $i }}</option>@endfor
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
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
            b.classList.remove('btn-primary');
            b.classList.add('btn-outline-primary');
        });
        this.classList.add('btn-primary');
        this.classList.remove('btn-outline-primary');
        const trip = this.dataset.trip;
        document.getElementById('tripInput').value = trip;
        document.getElementById('returnDateGroup').style.display = trip === 'round' ? '' : 'none';
    });
});
</script>
@endpush
