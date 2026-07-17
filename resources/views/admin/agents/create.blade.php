@extends('admin.layouts.app')
@section('title', 'Add New Agent')

@section('content')

    <!-- ===== PAGE HEADER ===== -->
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

            <!-- ===== PERSONAL INFORMATION ===== -->
            <div class="af-card">
                <h5 class="af-section-title"><i class="bi bi-person"></i> Personal Information</h5>

                <div class="af-grid-2">
                    <div class="af-field">
                        <label>First Name <span class="af-required">*</span></label>
                        <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror"
                               value="{{ old('first_name') }}" required>
                        @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="af-field">
                        <label>Last Name <span class="af-required">*</span></label>
                        <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror"
                               value="{{ old('last_name') }}" required>
                        @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="af-field">
                        <label>Email <span class="af-required">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" required>
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

            <!-- ===== COMPANY INFORMATION ===== -->
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
                        <input type="number" name="commission_rate" class="form-control" min="0" max="100" step="0.01"
                               value="{{ old('commission_rate', 0) }}">
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

@push('styles')
    <style>
        /* ===== PAGE HEADER ===== */
        .af-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 1.25rem;
        }
        .af-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--bs-body-color);
            margin: 0 0 4px;
        }
        .af-subtitle {
            font-size: 13px;
            color: var(--bs-secondary-color);
            margin: 0;
        }
        .af-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 16px;
            border: 1px solid var(--bs-border-color);
            border-radius: 8px;
            background: var(--bs-secondary-bg);
            color: var(--bs-secondary-color);
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: background .15s, color .15s;
        }
        .af-back-btn:hover {
            background: var(--bs-tertiary-bg);
            color: var(--bs-body-color);
            text-decoration: none;
        }

        /* ===== CONTENT WRAPPER ===== */
        .af-content {
            max-width: 760px;
            margin: 0 auto;
        }

        /* ===== ERROR CARD ===== */
        .af-error-card {
            background: #FCEBEB;
            border: 1px solid transparent;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.25rem;
        }
        [data-bs-theme="dark"] .af-error-card { background: #2e0a0a; }
        .af-error-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #A32D2D;
            margin-bottom: 6px;
        }
        [data-bs-theme="dark"] .af-error-title { color: #f08080; }
        .af-error-list {
            margin: 0;
            padding-left: 1.4rem;
            font-size: 12px;
            color: #A32D2D;
        }
        [data-bs-theme="dark"] .af-error-list { color: #f08080; }

        /* ===== CARD ===== */
        .af-card {
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 14px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.25rem;
        }

        /* ===== SECTION TITLE ===== */
        .af-section-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 600;
            color: var(--bs-body-color);
            margin: 0 0 1.1rem;
            padding-bottom: .85rem;
            border-bottom: 1px solid var(--bs-border-color);
        }
        .af-section-title i {
            font-size: 16px;
            color: var(--bs-primary);
        }

        /* ===== GRID & FIELDS ===== */
        .af-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .af-field label {
            font-size: 12px;
            font-weight: 500;
            color: var(--bs-secondary-color);
            margin-bottom: 6px;
            display: block;
        }
        .af-required { color: #A32D2D; }
        .af-field-spaced { margin-top: 1.25rem; }
        .af-field-help {
            font-size: 11px;
            color: var(--bs-secondary-color);
            margin-top: 5px;
        }

        .af-card .form-control,
        .af-card .form-select {
            border-radius: 8px;
            border-color: var(--bs-border-color);
            font-size: 13px;
        }
        .af-card .form-control:focus,
        .af-card .form-select:focus {
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 3px rgba(24, 95, 165, .12);
        }

        /* ===== SUBMIT BUTTON ===== */
        .af-submit-btn {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 11px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            background: var(--bs-primary);
            border: 1px solid var(--bs-primary);
            color: #fff;
            cursor: pointer;
            transition: opacity .15s;
        }
        .af-submit-btn:hover { opacity: .9; color: #fff; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .af-grid-2 { grid-template-columns: 1fr; }
        }
        @media (max-width: 480px) {
            .af-header { align-items: flex-start; }
        }
    </style>
@endpush
