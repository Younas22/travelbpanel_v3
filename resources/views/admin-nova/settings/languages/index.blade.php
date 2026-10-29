@extends('admin-nova.layouts.app')

@section('title', 'Manage Languages')

@section('content')
<div class="tbp-email-settings-page">
    @include('admin-nova.settings.languages._table')
    <p class="tbp-email-settings-note"><i class="bi bi-info-circle"></i> <span><strong>Note:</strong> Only active languages will be available in translation forms and frontend. Only one language can be set as default at a time.</span></p>
</div>
@endsection
