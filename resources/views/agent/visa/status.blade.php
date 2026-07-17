@extends('agent.layouts.app')
@section('title', 'Visa Application Status')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-passport"></i> Visa Application</h4>
    <a href="{{ route('agent.visa.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> All Applications
    </a>
</div>

@php
    $status = $visaRequest->status ?? 'pending';
    $statusColor = match($status) { 'approved' => 'success', 'rejected' => 'danger', default => 'warning' };
    $statusIcon  = match($status) { 'approved' => 'check-circle-fill', 'rejected' => 'x-circle-fill', default => 'clock' };
@endphp

<div class="alert alert-{{ $statusColor }}">
    <i class="bi bi-{{ $statusIcon }}"></i>
    Application Status: <strong>{{ ucfirst($status) }}</strong>
    @if($status === 'pending')
     — Under review by admin.
    @endif
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between">
        <h6 class="mb-0">Application #{{ str_pad($visaRequest->id, 6, '0', STR_PAD_LEFT) }}</h6>
        <span class="badge bg-{{ $statusColor }}">{{ ucfirst($status) }}</span>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <h6 class="text-muted small mb-2">Visa Details</h6>
                <table class="table table-sm table-borderless">
                    <tr><td class="text-muted">Visa Type</td><td><strong>{{ strtoupper($visaRequest->visa_type) }}</strong></td></tr>
                    <tr><td class="text-muted">Plan</td><td>{{ ucfirst($visaRequest->visa_plan) }}</td></tr>
                    <tr><td class="text-muted">Submitted</td><td>{{ $visaRequest->created_at->format('d M Y, h:i A') }}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted small mb-2">Applicant Details</h6>
                <table class="table table-sm table-borderless">
                    <tr><td class="text-muted">Full Name</td><td><strong>{{ $visaRequest->first_name }} {{ $visaRequest->middle_name }} {{ $visaRequest->surname }}</strong></td></tr>
                    <tr><td class="text-muted">Passport No</td><td><code>{{ $visaRequest->passport_no }}</code></td></tr>
                    <tr><td class="text-muted">Nationality</td><td>{{ ucfirst($visaRequest->nationality) }}</td></tr>
                    <tr><td class="text-muted">Gender</td><td>{{ ucfirst($visaRequest->gender) }}</td></tr>
                    <tr><td class="text-muted">Passport Expiry</td><td>{{ $visaRequest->passport_expiry_date->format('d M Y') }}</td></tr>
                </table>
            </div>
        </div>

        {{-- Documents --}}
        <hr>
        <h6 class="text-muted small mb-2">Submitted Documents</h6>
        <div class="d-flex flex-wrap gap-2">
            @foreach(['passport_front' => 'Passport Front', 'passport_back' => 'Passport Back', 'passport_photo' => 'Photo', 'other_document' => 'Other'] as $key => $label)
            @if($visaRequest->$key)
            <a href="{{ asset('storage/' . $visaRequest->$key) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-file-earmark"></i> {{ $label }}
            </a>
            @endif
            @endforeach
        </div>
    </div>
</div>
@endsection
