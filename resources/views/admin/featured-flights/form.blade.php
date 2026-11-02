@extends('admin.layouts.app')

@php $isEdit = $flight->exists; @endphp
@section('title', $isEdit ? 'Edit Featured Flight' : 'Add Featured Flight')

@section('content')
<div class="content-area p-4 tbp-hotel-module">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">{{ $isEdit ? 'Edit Featured Flight' : 'Add Featured Flight' }}</h4>
            <p class="text-muted mb-0">A deal card in "Featured Flight Deals"; clicking it searches this route one way for 1 adult</p>
        </div>
        <a href="{{ route('admin.featured-flights.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to List</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ $isEdit ? route('admin.featured-flights.update', $flight) : route('admin.featured-flights.store') }}" method="POST">
        @csrf
        @if($isEdit) @method('PUT') @endif
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Route</h5></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">From airport code <span class="text-danger">*</span></label>
                                <input type="text" name="from_code" class="form-control text-uppercase" value="{{ old('from_code', $flight->from_code) }}" maxlength="3" placeholder="e.g., LHE" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">From city <span class="text-danger">*</span></label>
                                <input type="text" name="from_city" class="form-control" value="{{ old('from_city', $flight->from_city) }}" maxlength="100" placeholder="e.g., Lahore" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">To airport code <span class="text-danger">*</span></label>
                                <input type="text" name="to_code" class="form-control text-uppercase" value="{{ old('to_code', $flight->to_code) }}" maxlength="3" placeholder="e.g., DXB" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">To city <span class="text-danger">*</span></label>
                                <input type="text" name="to_city" class="form-control" value="{{ old('to_city', $flight->to_city) }}" maxlength="100" placeholder="e.g., Dubai" required>
                            </div>
                            <div class="col-12"><small class="text-muted">3-letter IATA codes. The website shows the city and airport name from the airports list when the code is found there.</small></div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Airline &amp; fare</h5></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Airline name <span class="text-danger">*</span></label>
                                <input type="text" name="airline" class="form-control" value="{{ old('airline', $flight->airline) }}" maxlength="100" placeholder="e.g., Emirates" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Airline code <span class="text-danger">*</span></label>
                                <input type="text" name="airline_code" class="form-control text-uppercase" value="{{ old('airline_code', $flight->airline_code) }}" maxlength="3" placeholder="e.g., EK" required>
                                <small class="text-muted">IATA code, used for the logo</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Starting price (USD) <span class="text-danger">*</span></label>
                                <input type="number" name="price" class="form-control" value="{{ old('price', $flight->price) }}" min="0" step="0.01" required>
                                <small class="text-muted">Per adult; converted to the visitor's currency</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Cabin class <span class="text-danger">*</span></label>
                                <select name="cabin" class="form-select" required>
                                    @foreach(\App\Models\FeaturedFlight::CABINS as $value => $label)
                                        <option value="{{ $value }}" @selected(old('cabin', $flight->cabin) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Departure (days from today) <span class="text-danger">*</span></label>
                                <input type="number" name="days_ahead" class="form-control" value="{{ old('days_ahead', $flight->days_ahead) }}" min="0" max="365" required>
                                <small class="text-muted">Date shown on the card and used for the search</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Display</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Sort order</label>
                            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $flight->sort_order) }}" min="0">
                            <small class="text-muted">Lower numbers show first. The home page shows the first 6.</small>
                        </div>
                        <div class="form-check form-switch">
                            <input type="hidden" name="status" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" id="ffStatus" name="status" value="1" @checked(old('status', $flight->status))>
                            <label class="form-check-label" for="ffStatus">Active (show on website)</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check-circle"></i> {{ $isEdit ? 'Update Featured Flight' : 'Save Featured Flight' }}</button>
            </div>
        </div>
    </form>
</div>
@endsection
