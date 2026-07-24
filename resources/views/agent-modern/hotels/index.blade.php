@extends('agent-modern.layouts.app')
@section('title', 'My Hotels')

@section('content')

    <div class="ap-breadcrumb">
        <a href="{{ route('agent.dashboard') }}">Dashboard</a>
        <i class="bi bi-chevron-right"></i>
        <span>My Hotels</span>
    </div>

    <div class="ap-page-header">
        <div class="ap-page-header-left">
            <div class="ap-icon-badge"><i class="bi bi-building"></i></div>
            <div>
                <h4 class="ap-page-title">My Hotels</h4>
                <p class="ap-page-sub">Manage your hotel listings</p>
            </div>
        </div>
        <a href="{{ route('agent.hotels.create') }}" class="ap-btn-primary">
            <i class="bi bi-plus-lg"></i> Add Hotel
        </a>
    </div>

    <div class="am-card">
        @if($hotels->isEmpty())
            <div class="am-empty">
                <i class="bi bi-building"></i>
                <h6>No hotels yet</h6>
                <p class="mb-3">Start by adding your first hotel listing.</p>
                <a href="{{ route('agent.hotels.create') }}" class="ap-btn-primary">
                    <i class="bi bi-plus-lg"></i> Add Hotel
                </a>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Hotel Name</th>
                        <th>Location</th>
                        <th>Type</th>
                        <th>Stars</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($hotels as $hotel)
                    <tr>
                        <td style="color: color-mix(in srgb, var(--text-color) 50%, transparent); font-size: 12px;">{{ $hotel->id }}</td>
                        <td style="font-weight: 650;">{{ $hotel->name }}</td>
                        <td>{{ $hotel->location?->city ?? '—' }}@if($hotel->location?->country), {{ $hotel->location->country }}@endif</td>
                        <td>{{ ucfirst($hotel->type) }}</td>
                        <td>
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bi bi-star{{ $i <= ($hotel->stars ?? 0) ? '-fill' : '' }}" style="font-size: 11px; color: {{ $i <= ($hotel->stars ?? 0) ? '#f59e0b' : 'var(--border-color)' }};"></i>
                            @endfor
                        </td>
                        <td>
                            @php $approval = $hotel->approval_status ?? 'approved'; @endphp
                            @if($approval === 'pending')
                                <span class="badge bg-warning"><i class="bi bi-clock"></i> Pending</span>
                            @elseif($approval === 'rejected')
                                <span class="badge bg-danger"><i class="bi bi-x-lg"></i> Rejected</span>
                            @else
                                <span class="badge bg-success"><i class="bi bi-check-lg"></i> Approved</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('agent.hotels.edit', $hotel->id) }}" class="ap-btn-outline" style="padding: 6px 12px; font-size: 11.5px;">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <form method="POST" action="{{ route('agent.hotels.destroy', $hotel->id) }}" class="d-inline" onsubmit="return confirm('Delete this hotel?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="ap-btn-danger" style="padding: 6px 12px; font-size: 11.5px;">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($hotels->hasPages())
        <div style="padding: var(--am-space-4) var(--am-space-5); border-top: 1px solid var(--border-color);">
            {{ $hotels->links() }}
        </div>
        @endif
        @endif
    </div>

@endsection
