@extends('admin.layouts.app')

@section('title', 'Contact Message Details')

@section('content')
<div class="content-area">
    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ route('admin.contact-messages.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Messages
        </a>
    </div>

    <!-- Message Header -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-gradient-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">Message #CM{{ str_pad($contactMessage->id, 6, '0', STR_PAD_LEFT) }}</h4>
                    <small>Received: {{ $contactMessage->created_at->format('F j, Y \a\t g:i A') }} ({{ $contactMessage->created_at->diffForHumans() }})</small>
                </div>
                <div>
                    <form method="POST" action="{{ route('admin.contact-messages.update-status', $contactMessage->id) }}" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <select name="status" class="form-select form-select-sm bg-white" onchange="this.form.submit()">
                            <option value="new" {{ $contactMessage->status === 'new' ? 'selected' : '' }}>New</option>
                            <option value="read" {{ $contactMessage->status === 'read' ? 'selected' : '' }}>Read</option>
                            <option value="replied" {{ $contactMessage->status === 'replied' ? 'selected' : '' }}>Replied</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row">
                <!-- Contact Information -->
                <div class="col-md-6">
                    <h5 class="border-bottom pb-2 mb-3">
                        <i class="bi bi-person-circle text-primary"></i> Contact Information
                    </h5>
                    <table class="table table-sm">
                        <tr>
                            <th width="140">Full Name:</th>
                            <td class="fw-semibold">{{ $contactMessage->full_name }}</td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>
                                <a href="mailto:{{ $contactMessage->email }}" class="text-decoration-none">
                                    <i class="bi bi-envelope"></i> {{ $contactMessage->email }}
                                </a>
                            </td>
                        </tr>
                        @if($contactMessage->phone)
                        <tr>
                            <th>Phone:</th>
                            <td>
                                <a href="tel:{{ $contactMessage->phone }}" class="text-decoration-none">
                                    <i class="bi bi-telephone"></i> {{ $contactMessage->phone }}
                                </a>
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>

                <!-- Message Details -->
                <div class="col-md-6">
                    <h5 class="border-bottom pb-2 mb-3">
                        <i class="bi bi-info-circle text-primary"></i> Message Details
                    </h5>
                    <table class="table table-sm">
                        <tr>
                            <th width="140">Subject:</th>
                            <td>
                                @php
                                    $subjectLabels = [
                                        'booking' => 'Booking Assistance',
                                        'cancellation' => 'Cancellation/Refund',
                                        'modification' => 'Flight Modification',
                                        'complaint' => 'Complaint',
                                        'feedback' => 'Feedback',
                                        'partnership' => 'Business Partnership',
                                        'other' => 'Other'
                                    ];
                                    $subjectText = $subjectLabels[$contactMessage->subject] ?? ucfirst($contactMessage->subject);
                                @endphp
                                <span class="badge bg-primary">{{ $subjectText }}</span>
                            </td>
                        </tr>
                        @if($contactMessage->booking_ref)
                        <tr>
                            <th>Booking Reference:</th>
                            <td><span class="badge bg-secondary">{{ $contactMessage->booking_ref }}</span></td>
                        </tr>
                        @endif
                        <tr>
                            <th>Status:</th>
                            <td>
                                @php
                                    $statusColors = [
                                        'new' => 'warning',
                                        'read' => 'info',
                                        'replied' => 'success'
                                    ];
                                    $statusColor = $statusColors[$contactMessage->status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $statusColor }}">{{ ucfirst($contactMessage->status) }}</span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Message Content -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-chat-left-text text-primary"></i> Message Content
            </h5>
        </div>
        <div class="card-body">
            <div class="p-3 bg-light rounded">
                <p style="white-space: pre-wrap; line-height: 1.8;">{{ $contactMessage->message }}</p>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex gap-2 justify-content-between">
                <div>
                    <a href="mailto:{{ $contactMessage->email }}?subject=Re: {{ urlencode($subjectText) }}&body={{ urlencode('Dear ' . $contactMessage->first_name . ',' . "\n\n") }}" 
                       class="btn btn-success">
                        <i class="bi bi-reply"></i> Reply via Email
                    </a>
                    @if($contactMessage->phone)
                    <a href="tel:{{ $contactMessage->phone }}" class="btn btn-info">
                        <i class="bi bi-telephone"></i> Call Customer
                    </a>
                    @endif
                </div>
                <div>
                    <form method="POST" action="{{ route('admin.contact-messages.destroy', $contactMessage->id) }}" 
                          onsubmit="return confirm('Are you sure you want to delete this message?');" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Delete Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
</style>
@endsection