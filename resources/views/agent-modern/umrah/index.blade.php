@extends('agent-modern.layouts.app')
@section('title', 'My Umrah Packages')

@section('content')

    <div class="ap-breadcrumb">
        <a href="{{ route('agent.dashboard') }}">Dashboard</a>
        <i class="bi bi-chevron-right"></i>
        <span>My Umrah Packages</span>
    </div>

    <div class="ap-page-header">
        <div class="ap-page-header-left">
            <div class="ap-icon-badge"><i class="bi bi-moon-stars"></i></div>
            <div>
                <h4 class="ap-page-title">My Umrah Packages</h4>
                <p class="ap-page-sub">Manage your Umrah listings</p>
            </div>
        </div>
        <a href="{{ route('agent.umrah.create') }}" class="ap-btn-primary">
            <i class="bi bi-plus-lg"></i> Add Package
        </a>
    </div>

    <div class="am-card">
        @if($packages->isEmpty())
            <div class="am-empty">
                <i class="bi bi-moon-stars"></i>
                <h6>No Umrah packages yet</h6>
                <p class="mb-3">Start by adding your first Umrah package.</p>
                <a href="{{ route('agent.umrah.create') }}" class="ap-btn-primary">
                    <i class="bi bi-plus-lg"></i> Add Your First Package
                </a>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Package Name</th>
                        <th>Location</th>
                        <th>Type</th>
                        <th>Price</th>
                        <th>Duration</th>
                        <th>Approval</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($packages as $package)
                    <tr>
                        <td style="color: color-mix(in srgb, var(--text-color) 50%, transparent); font-size: 12px;">{{ $package->id }}</td>
                        <td style="font-weight: 650;">{{ $package->name }}</td>
                        <td>{{ $package->loaction ?? '—' }}</td>
                        <td>{{ ucfirst($package->packege_type ?? '—') }}</td>
                        <td style="font-weight: 650;">{{ $package->currceny }} {{ number_format($package->price, 0) }}</td>
                        <td>{{ $package->duration ?? '—' }}</td>
                        <td>
                            @php $approval = $package->approval_status ?? 'approved'; @endphp
                            @if($approval === 'pending')
                                <span class="badge bg-warning"><i class="bi bi-clock"></i> Pending</span>
                            @elseif($approval === 'rejected')
                                <span class="badge bg-danger"><i class="bi bi-x-lg"></i> Rejected</span>
                            @else
                                <span class="badge bg-success"><i class="bi bi-check-lg"></i> Approved</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('agent.umrah.edit', $package->id) }}" class="ap-btn-outline" style="padding: 6px 12px; font-size: 11.5px;">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <form method="POST" action="{{ route('agent.umrah.destroy', $package->id) }}" class="d-inline" onsubmit="return confirm('Delete this package?')">
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
        <div style="padding: var(--am-space-4) var(--am-space-5); border-top: 1px solid var(--border-color);">{{ $packages->links() }}</div>
        @endif
    </div>

@endsection
