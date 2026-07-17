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

@push('styles')
    <style>
        /* ===== PAGE HEADER ===== */
        .pp-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 1.25rem;
        }
        .pp-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--bs-body-color);
            margin: 0 0 4px;
        }
        .pp-subtitle {
            font-size: 13px;
            color: var(--bs-secondary-color);
            margin: 0;
        }
        .pp-back-btn {
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
        .pp-back-btn:hover {
            background: var(--bs-tertiary-bg);
            color: var(--bs-body-color);
            text-decoration: none;
        }

        /* ===== CONTENT WRAPPER ===== */
        .pp-content {
            max-width: 900px;
            margin: 0 auto;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 480px) {
            .pp-header { align-items: flex-start; }
        }
    </style>
@endpush
