@extends('admin-nova.layouts.app')

@section('title', 'Edit Agent: ' . $agent->full_name)

@section('content')
<div class="tbp-agent-form-page">
    <div class="tbp-agent-form-header">
        <div><h1>Edit Agent</h1><p>Update agent account information for {{ $agent->full_name }}</p></div>
        <a href="{{ route('admin.agents.show', $agent) }}" class="tbp-agent-form-btn"><i class="bi bi-arrow-left"></i> Back</a>
    </div>

    <form method="POST" action="{{ route('admin.agents.update', $agent) }}">
        @csrf
        @method('PUT')
        @include('admin-nova.agents._form', ['agent' => $agent])
        <div class="tbp-agent-form-actions">
            <a href="{{ route('admin.agents.show', $agent) }}" class="tbp-agent-form-btn">Cancel</a>
            <button type="submit" class="tbp-agent-form-btn is-primary tbp-agent-form-submit" data-loading="Saving…"><i class="bi bi-upload"></i> <span>Save Changes</span></button>
        </div>
    </form>
</div>
@endsection
