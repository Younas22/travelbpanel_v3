@extends('admin.layouts.app')

@section('title', 'Visa Request Details')

@section('content')
<style>
.detail-card {
    background: var(--bs-body-bg, white);
    border: 1px solid var(--bs-border-color, #e5e7eb);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

[data-bs-theme="dark"] .detail-card {
    background: var(--bs-dark, #212529);
    border-color: var(--bs-border-color-translucent, #495057);
    color: var(--bs-body-color, #fff);
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid var(--bs-border-color, #f1f3f4);
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-label {
    font-weight: 600;
    color: var(--bs-body-color, #374151);
    min-width: 140px;
}

.detail-value {
    color: var(--bs-secondary-color, #6b7280);
    text-align: right;
    flex: 1;
}

[data-bs-theme="dark"] .detail-row {
    border-bottom-color: var(--bs-border-color-translucent, #495057);
}

[data-bs-theme="dark"] .detail-label {
    color: var(--bs-body-color, #fff);
    font-weight: 600;
}

[data-bs-theme="dark"] .detail-value {
    color: var(--bs-body-color, #fff);
}

/* Section headers dark mode */
[data-bs-theme="dark"] .section-header h5 {
    color: var(--bs-body-color, #fff);
}

.document-preview {
    max-width: 200px;
    border-radius: 8px;
    cursor: pointer;
    transition: transform 0.2s;
}

.document-preview:hover {
    transform: scale(1.05);
}

.section-header {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 2px solid var(--bs-border-color, #e5e7eb);
}

.section-icon {
    width: 24px;
    height: 24px;
    margin-right: 12px;
    color: #6366f1;
    background: transparent;
}

[data-bs-theme="dark"] .section-header {
    border-bottom-color: var(--bs-border-color-translucent, #495057);
}

[data-bs-theme="dark"] .section-icon {
    color: #818cf8;
}

.badge-status {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.back-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 24px;
    color: #6366f1;
    text-decoration: none;
}

.back-button:hover {
    color: #4f46e5;
}
</style>

<div class="content-area">
    <!-- Back Button -->
    <a href="{{ route('admin.visa-requests.visaindex') }}" class="back-button">
        <i class="bi bi-arrow-left"></i>
        Back to Visa Requests
    </a>

    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-1">Visa Request Details</h2>
                <p class="text-muted mb-0">Request ID: #VR{{ str_pad($visaRequest->id, 6, '0', STR_PAD_LEFT) }}</p>
            </div>
            <div class="col-md-4 text-end">
                <span class="badge bg-primary">{{ ucfirst($visaRequest->visa_category) }} Visa</span>
                <div class="text-muted mt-1">Applied: {{ $visaRequest->created_at->format('M j, Y g:i A') }}</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Personal Information -->
        <div class="col-md-6">
            <div class="detail-card">
                <div class="section-header">
                    <i class="bi bi-person section-icon"></i>
                    <h5 class="mb-0">Personal Information</h5>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Full Name:</span>
                    <span class="detail-value fw-semibold">
                        {{ trim($visaRequest->first_name . ' ' . ($visaRequest->middle_name ?? '') . ' ' . $visaRequest->surname) }}
                    </span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Father's Name:</span>
                    <span class="detail-value">{{ $visaRequest->father_name ?? 'N/A' }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Mother's Name:</span>
                    <span class="detail-value">{{ $visaRequest->mother_name ?? 'N/A' }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Place of Birth:</span>
                    <span class="detail-value">{{ $visaRequest->place_birth ?? 'N/A' }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Gender:</span>
                    <span class="detail-value">{{ ucfirst($visaRequest->gender) }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Marital Status:</span>
                    <span class="detail-value">{{ ucfirst($visaRequest->marital_status) }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Religion:</span>
                    <span class="detail-value">{{ ucfirst($visaRequest->religion) }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Occupation:</span>
                    <span class="detail-value">{{ $visaRequest->occupation ?? 'N/A' }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Nationality:</span>
                    <span class="detail-value">
                        <span class="badge bg-light text-dark">{{ ucfirst($visaRequest->nationality) }}</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Visa & Passport Information -->
        <div class="col-md-6">
            <div class="detail-card">
                <div class="section-header">
                    <i class="bi bi-passport section-icon"></i>
                    <h5 class="mb-0">Visa & Passport Information</h5>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Visa Category:</span>
                    <span class="detail-value">
                        <span class="badge bg-primary">{{ ucfirst($visaRequest->visa_category) }}</span>
                    </span>
                </div>
                
                @if($visaRequest->visa_type)
                <div class="detail-row">
                    <span class="detail-label">Visa Type:</span>
                    <span class="detail-value">{{ ucfirst($visaRequest->visa_type) }}</span>
                </div>
                @endif
                
                @if($visaRequest->visa_plan)
                <div class="detail-row">
                    <span class="detail-label">Visa Plan:</span>
                    <span class="detail-value">{{ ucfirst($visaRequest->visa_plan) }}</span>
                </div>
                @endif
                
                <div class="detail-row">
                    <span class="detail-label">Passport Number:</span>
                    <span class="detail-value fw-semibold">{{ $visaRequest->passport_no }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Issue Date:</span>
                    <span class="detail-value">
                        {{ $visaRequest->passport_issue_date ? $visaRequest->passport_issue_date->format('M j, Y') : 'N/A' }}
                    </span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Expiry Date:</span>
                    <span class="detail-value">
                        {{ $visaRequest->passport_expiry_date ? $visaRequest->passport_expiry_date->format('M j, Y') : 'N/A' }}
                        @if($visaRequest->passport_expiry_date && $visaRequest->passport_expiry_date->isPast())
                            <span class="badge bg-danger ms-2">Expired</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Guarantor Information -->
    @if($visaRequest->guarantor_name)
    <div class="detail-card">
        <div class="section-header">
            <i class="bi bi-shield-check section-icon"></i>
            <h5 class="mb-0">Guarantor Information</h5>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="detail-row">
                    <span class="detail-label">Guarantor Name:</span>
                    <span class="detail-value fw-semibold">{{ $visaRequest->guarantor_name }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Nationality:</span>
                    <span class="detail-value">{{ ucfirst($visaRequest->guarantor_nationality ?? 'N/A') }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Relation:</span>
                    <span class="detail-value">{{ ucfirst($visaRequest->guarantor_relation ?? 'N/A') }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Emirates ID:</span>
                    <span class="detail-value">{{ $visaRequest->guarantor_emirates_id ?? 'N/A' }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Passport Number:</span>
                    <span class="detail-value">{{ $visaRequest->guarantor_passport_no ?? 'N/A' }}</span>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="detail-row">
                    <span class="detail-label">Employer:</span>
                    <span class="detail-value">{{ $visaRequest->employer_name ?? 'N/A' }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Company Contact:</span>
                    <span class="detail-value">{{ $visaRequest->company_contact ?? 'N/A' }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Visa Number:</span>
                    <span class="detail-value">{{ $visaRequest->guarantor_visa_no ?? 'N/A' }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Visa Expiry:</span>
                    <span class="detail-value">
                        {{ $visaRequest->guarantor_visa_expiry ? $visaRequest->guarantor_visa_expiry->format('M j, Y') : 'N/A' }}
                    </span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Mobile:</span>
                    <span class="detail-value">{{ $visaRequest->guarantor_mobile ?? 'N/A' }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value">{{ $visaRequest->guarantor_email ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <!-- Documents -->
        <div class="col-md-6">
            <div class="detail-card">
                <div class="section-header">
                    <i class="bi bi-file-earmark section-icon"></i>
                    <h5 class="mb-0">Documents</h5>
                </div>
                
                @if($visaRequest->passport_front)
                <div class="detail-row">
                    <span class="detail-label">Passport Front:</span>
                    <span class="detail-value">
                        <a href="{{ Storage::url($visaRequest->passport_front) }}" target="_blank">
                            <img src="{{ Storage::url($visaRequest->passport_front) }}" 
                                 alt="Passport Front" class="document-preview">
                        </a>
                    </span>
                </div>
                @endif
                
                @if($visaRequest->passport_back)
                <div class="detail-row">
                    <span class="detail-label">Passport Back:</span>
                    <span class="detail-value">
                        <a href="{{ Storage::url($visaRequest->passport_back) }}" target="_blank">
                            <img src="{{ Storage::url($visaRequest->passport_back) }}" 
                                 alt="Passport Back" class="document-preview">
                        </a>
                    </span>
                </div>
                @endif
                
                @if($visaRequest->passport_photo)
                <div class="detail-row">
                    <span class="detail-label">Passport Photo:</span>
                    <span class="detail-value">
                        <a href="{{ Storage::url($visaRequest->passport_photo) }}" target="_blank">
                            <img src="{{ Storage::url($visaRequest->passport_photo) }}" 
                                 alt="Passport Photo" class="document-preview">
                        </a>
                    </span>
                </div>
                @endif
                
                @if($visaRequest->other_document)
                <div class="detail-row">
                    <span class="detail-label">Other Document:</span>
                    <span class="detail-value">
                        <a href="{{ Storage::url($visaRequest->other_document) }}" 
                           target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-download"></i> Download
                        </a>
                    </span>
                </div>
                @endif
                
                @if(!$visaRequest->passport_front && !$visaRequest->passport_back && !$visaRequest->passport_photo && !$visaRequest->other_document)
                <div class="text-center text-muted py-3">
                    <i class="bi bi-file-x display-6"></i>
                    <p class="mb-0">No documents uploaded</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Payment Information -->
        <div class="col-md-6">
            <div class="detail-card">
                <div class="section-header">
                    <i class="bi bi-credit-card section-icon"></i>
                    <h5 class="mb-0">Payment Information</h5>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Receipt Number:</span>
                    <span class="detail-value">{{ $visaRequest->receipt_no ?? 'N/A' }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Receipt Amount:</span>
                    <span class="detail-value">
                        @if($visaRequest->receipt_amount)
                            <span class="fw-semibold">AED {{ number_format($visaRequest->receipt_amount) }}</span>
                        @else
                            N/A
                        @endif
                    </span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Receipt Date:</span>
                    <span class="detail-value">
                        {{ $visaRequest->receipt_date ? $visaRequest->receipt_date->format('M j, Y') : 'N/A' }}
                    </span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Visa Payment Date:</span>
                    <span class="detail-value">
                        {{ $visaRequest->visa_payment_date ? $visaRequest->visa_payment_date->format('M j, Y') : 'N/A' }}
                    </span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Ticket/OTB Date:</span>
                    <span class="detail-value">
                        {{ $visaRequest->ticket_otb_date ? $visaRequest->ticket_otb_date->format('M j, Y') : 'N/A' }}
                    </span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Security Deposit Date:</span>
                    <span class="detail-value">
                        {{ $visaRequest->security_deposit_date ? $visaRequest->security_deposit_date->format('M j, Y') : 'N/A' }}
                    </span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Terms Agreed:</span>
                    <span class="detail-value">
                        @if($visaRequest->agreed_terms)
                            <span class="badge bg-success">Yes</span>
                        @else
                            <span class="badge bg-warning">No</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline -->
    <div class="detail-card">
        <div class="section-header">
            <i class="bi bi-clock-history section-icon"></i>
            <h5 class="mb-0">Timeline</h5>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Application Submitted</h6>
                            <p class="text-muted mb-0">{{ $visaRequest->created_at->format('M j, Y g:i A') }}</p>
                        </div>
                    </div>
                    
                    @if($visaRequest->receipt_date)
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Receipt Generated</h6>
                            <p class="text-muted mb-0">{{ $visaRequest->receipt_date->format('M j, Y') }}</p>
                        </div>
                    </div>
                    @endif
                    
                    @if($visaRequest->visa_payment_date)
                    <div class="timeline-item">
                        <div class="timeline-marker bg-info"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Visa Payment Made</h6>
                            <p class="text-muted mb-0">{{ $visaRequest->visa_payment_date->format('M j, Y') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e5e7eb;
}

.timeline-item {
    position: relative;
    margin-bottom: 24px;
}

.timeline-marker {
    position: absolute;
    left: -23px;
    top: 4px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 3px solid white;
    box-shadow: 0 0 0 2px #e5e7eb;
}

.timeline-content {
    background: #f9fafb;
    padding: 16px;
    border-radius: 8px;
    border-left: 4px solid #6366f1;
}


[data-bs-theme="dark"] .timeline-content {
    background: var(--bs-dark, #212529);
}
</style>

@endsection