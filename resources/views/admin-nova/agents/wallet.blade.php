@extends('admin-nova.layouts.app')

@section('title', 'Agent Wallet — ' . $agent->full_name)

@push('styles')
@include('admin-nova.agents._styles')
@endpush

@section('content')
<div id="agPage" class="tt-fade-in font-jakarta">

    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-lg font-bold text-novatext">Agent Wallet</h1>
            <p class="text-xs text-novamuted mt-1">{{ $agent->full_name }} ({{ $agent->agent_code }})</p>
        </div>
        <a href="{{ route('admin.agents.show', $agent) }}" class="ag-btn-nova px-4 py-2.5 text-xs font-semibold">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back to Agent
        </a>
    </div>

    @include('admin-nova.agents._tab_wallet')
</div>
@endsection
