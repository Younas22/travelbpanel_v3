@extends('admin-nova.layouts.app')

@section('title', 'Add New Agent')

@section('content')
<div class="tbp-agent-form-page">
    <div class="tbp-agent-form-header">
        <div><h1>Add New Agent</h1><p>Create a new B2B agent account</p></div>
        <a href="{{ route('admin.agents.index') }}" class="tbp-agent-form-btn"><i class="bi bi-arrow-left"></i> Back</a>
    </div>

    <form method="POST" action="{{ route('admin.agents.store') }}">
        @csrf
        @include('admin-nova.agents._form', ['agent' => null])
        <div class="tbp-agent-form-actions">
            <a href="{{ route('admin.agents.index') }}" class="tbp-agent-form-btn">Cancel</a>
            <button type="submit" class="tbp-agent-form-btn is-primary tbp-agent-form-submit" data-loading="Creating…"><i class="bi bi-plus-lg"></i> <span>Create Agent</span></button>
        </div>
    </form>
</div>
@endsection
