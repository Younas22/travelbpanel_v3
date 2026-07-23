@extends('admin.layouts.app')
@section('title', 'Permissions — ' . $agent->full_name)

@section('content')

    <!-- ===== PAGE HEADER ===== -->
    <div class="pp-header">
        <div>
            <h2 class="pp-title">Agent Permissions</h2>
            <p class="pp-subtitle">{{ $agent->full_name }} ({{ $agent->agent_code }})</p>
        </div>
        <a href="{{ route('admin.agents.show', $agent) }}" class="pp-back-btn">
            <i class="bi bi-arrow-left"></i> Back to Agent
        </a>
    </div>

    <div class="pp-content">
        @include('admin.agents._tab_permissions')
    </div>

@endsection
