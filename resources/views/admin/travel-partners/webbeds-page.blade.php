@extends('admin.layouts.app')

@section('title', $section === 'logs' ? 'WebBeds Logs' : 'WebBeds Import Data')

@section('content')
    <div class="content-area">
        <div class="tbp-partner-edit-page tbp-webbeds-page">
            <a href="{{ route('admin.travel-partners.edit', $partner) }}" class="tbp-partner-edit-back">
                <i class="bi bi-arrow-left"></i> Back to WebBeds settings
            </a>
            <div class="tbp-partner-edit-header">
                <div class="tbp-partner-edit-header-row">
                    <h2 class="tbp-partner-edit-title">{{ $section === 'logs' ? 'Logs' : 'Import Data' }}</h2>
                    <span class="tbp-webbeds-int-badge"><i class="bi bi-plug"></i> WebBeds</span>
                </div>
                <p class="tbp-partner-edit-subtitle">
                    {{ $section === 'logs'
                        ? 'Every WebBeds API call, one log per customer journey — search, details, room block, payment and booking. Failed bookings are marked in red.'
                        : 'Import hotels for every WebBeds city into the local database — refreshed automatically every month.' }}
                </p>
                <div class="tbp-webbeds-tabs">
                    <a href="{{ route('admin.travel-partners.edit', $partner) }}" class="tbp-partner-edit-btn tbp-partner-edit-btn-outline"><i class="bi bi-gear"></i> Settings</a>
                    <a href="{{ route('admin.travel-partners.webbeds.import', $partner) }}" class="tbp-partner-edit-btn {{ $section === 'import' ? 'tbp-partner-edit-btn-primary' : 'tbp-partner-edit-btn-outline' }}"><i class="bi bi-cloud-arrow-up"></i> Import Data</a>
                    <a href="{{ route('admin.travel-partners.webbeds.logs', $partner) }}" class="tbp-partner-edit-btn {{ $section === 'logs' ? 'tbp-partner-edit-btn-primary' : 'tbp-partner-edit-btn-outline' }}"><i class="bi bi-journal-text"></i> Logs</a>
                </div>
            </div>

            @include($section === 'logs' ? 'admin.travel-partners._webbeds-logs' : 'admin.travel-partners._webbeds-import')
        </div>
    </div>
@endsection
