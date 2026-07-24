@extends('admin-modern.layouts.app')
@section('title', 'Add New Agent')

@section('content')

    <div class="af-header">
        <div>
            <h2 class="af-title">Add New Agent</h2>
            <p class="af-subtitle">Create a new B2B agent account</p>
        </div>
        <a href="{{ route('admin.agents.index') }}" class="af-back-btn">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="af-content">

        @if($errors->any())
            <div class="af-error-card">
                <div class="af-error-title"><i class="bi bi-exclamation-triangle"></i> Please fix the following:</div>
                <ul class="af-error-list">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.agents.store') }}">
            @csrf

            <div class="af-card">
                <h5 class="af-section-title"><i class="bi bi-person"></i> Personal Information</h5>
                <div class="af-grid-2">
                    <div class="af-field">
                        <label>First Name <span class="af-required">*</span></label>
                        <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" required>
                        @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="af-field">
                        <label>Last Name <span class="af-required">*</span></label>
                        <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" required>
                        @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="af-field">
                        <label>Email <span class="af-required">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="af-field">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                    </div>
                    <div class="af-field">
                        <label>Password <span class="af-required">*</span></label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="af-field">
                        <label>Confirm Password <span class="af-required">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                </div>
            </div>

            <div class="af-card">
                <h5 class="af-section-title"><i class="bi bi-building"></i> Company Information</h5>
                <div class="af-grid-2">
                    <div class="af-field">
                        <label>Company Name</label>
                        <input type="text" name="company_name" class="form-control" value="{{ old('company_name') }}">
                    </div>
                    <div class="af-field">
                        <label>Company Phone</label>
                        <input type="text" name="company_phone" class="form-control" value="{{ old('company_phone') }}">
                    </div>
                    <div class="af-field">
                        <label>CNIC / Business Reg No.</label>
                        <input type="text" name="cnic_or_reg_number" class="form-control" value="{{ old('cnic_or_reg_number') }}">
                    </div>
                    <div class="af-field">
                        <label>Commission Rate (%)</label>
                        <input type="number" name="commission_rate" class="form-control" min="0" max="100" step="0.01" value="{{ old('commission_rate', 0) }}">
                    </div>
                </div>

                <div class="af-field af-field-spaced">
                    <label>Company Address</label>
                    <textarea name="company_address" class="form-control" rows="2">{{ old('company_address') }}</textarea>
                </div>

                <div class="af-field af-field-spaced">
                    <label>Internal Notes</label>
                    <textarea name="internal_notes" class="form-control" rows="2" placeholder="Admin-only notes...">{{ old('internal_notes') }}</textarea>
                    <div class="af-field-help">Visible only to admins, not to the agent</div>
                </div>
            </div>

            <button type="submit" class="af-submit-btn">
                <i class="bi bi-person-plus"></i> Create Agent
            </button>
        </form>
    </div>

@endsection
