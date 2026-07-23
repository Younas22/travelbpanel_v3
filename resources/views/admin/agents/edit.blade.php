@extends('admin.layouts.app')
@section('title', 'Edit Agent: ' . $agent->full_name)

@section('content')

    <!-- ===== PAGE HEADER ===== -->
    <div class="af-header">
        <div>
            <h2 class="af-title">Edit Agent</h2>
            <p class="af-subtitle">{{ $agent->full_name }}</p>
        </div>
        <a href="{{ route('admin.agents.show', $agent) }}" class="af-back-btn">
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

        <form method="POST" action="{{ route('admin.agents.update', $agent) }}">
            @csrf
            @method('PUT')

            <!-- ===== PERSONAL INFORMATION ===== -->
            <div class="af-card">
                <h5 class="af-section-title"><i class="bi bi-person"></i> Personal Information</h5>

                <div class="af-grid-2">
                    <div class="af-field">
                        <label>First Name <span class="af-required">*</span></label>
                        <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror"
                               value="{{ old('first_name', $agent->first_name) }}" required>
                        @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="af-field">
                        <label>Last Name <span class="af-required">*</span></label>
                        <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror"
                               value="{{ old('last_name', $agent->last_name) }}" required>
                        @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="af-field">
                        <label>Email <span class="af-required">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $agent->email) }}" required>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="af-field">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $agent->phone) }}">
                    </div>
                </div>
            </div>

            <!-- ===== COMPANY INFORMATION ===== -->
            <div class="af-card">
                <h5 class="af-section-title"><i class="bi bi-building"></i> Company Information</h5>

                <div class="af-grid-2">
                    <div class="af-field">
                        <label>Company Name</label>
                        <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $agent->company_name) }}">
                    </div>

                    <div class="af-field">
                        <label>Company Phone</label>
                        <input type="text" name="company_phone" class="form-control" value="{{ old('company_phone', $agent->company_phone) }}">
                    </div>

                    <div class="af-field">
                        <label>CNIC / Reg No.</label>
                        <input type="text" name="cnic_or_reg_number" class="form-control" value="{{ old('cnic_or_reg_number', $agent->cnic_or_reg_number) }}">
                    </div>

                    <div class="af-field">
                        <label>Commission Rate (%)</label>
                        <input type="number" name="commission_rate" class="form-control" min="0" max="100" step="0.01"
                               value="{{ old('commission_rate', $agent->commission_rate) }}">
                    </div>
                </div>

                <div class="af-field af-field-spaced">
                    <label>Company Address</label>
                    <textarea name="company_address" class="form-control" rows="2">{{ old('company_address', $agent->company_address) }}</textarea>
                </div>

                <div class="af-field af-field-spaced">
                    <label>Internal Notes</label>
                    <textarea name="internal_notes" class="form-control" rows="2">{{ old('internal_notes', $agent->internal_notes) }}</textarea>
                    <div class="af-field-help">Visible only to admins, not to the agent</div>
                </div>
            </div>

            <!-- ===== ACTIONS ===== -->
            <div class="af-actions">
                <a href="{{ route('admin.agents.show', $agent) }}" class="af-btn af-btn-outline">Cancel</a>
                <button type="submit" class="af-btn af-btn-primary">
                    <i class="bi bi-check-circle"></i> Save Changes
                </button>
            </div>
        </form>
    </div>

@endsection
