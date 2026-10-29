@extends('admin-nova.layouts.app')

@section('title', 'Permissions — ' . $agent->full_name)

@section('content')
<div class="tbp-agent-detail-page">
    @include('admin-nova.agents._detail_header', ['activeTab' => 'permissions'])
    @include('admin-nova.agents._tab_permissions')
</div>
@endsection
