@extends($layout ?? 'admin.layouts.app')
@section('title', 'Umrah Inclusions')

@section('content')

    <!-- ===== BREADCRUMB ===== -->
    <div class="tex-breadcrumb">
        <a href="{{ route('agent.dashboard') }}">Dashboard</a>
        <i class="bi bi-chevron-right"></i>
        <a href="{{ route('agent.umrah.index') }}">My Umrah</a>
        <i class="bi bi-chevron-right"></i>
        <span>Inclusions</span>
    </div>

    <!-- ===== PAGE HEADER ===== -->
    <div class="tex-header">
        <div class="tex-header-left">
            <div class="tex-header-icon"><i class="bi bi-check-circle"></i></div>
            <div>
                <h2 class="tex-title">Umrah Inclusions</h2>
                <p class="tex-subtitle">Manage inclusions for Umrah packages</p>
            </div>
        </div>
        <button onclick="openModal('addModal')" class="tex-add-btn">
            <i class="bi bi-plus-circle"></i> Add Inclusion
        </button>
    </div>

    <!-- ===== TABLE CARD ===== -->
    <div class="tex-card">
        <div class="table-responsive">
            <table class="tex-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Created At</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($inclusions as $inclusion)
                    <tr>
                        <td><span class="tex-meta">{{ $inclusion->id }}</span></td>
                        <td><span class="tex-name">{{ $inclusion->name }}</span></td>
                        <td><span class="tex-meta">{{ $inclusion->created_at->format('d M Y') }}</span></td>
                        <td>
                            <div class="tex-actions">
                                <button onclick="openModal('editModal{{ $inclusion->id }}')" class="tex-btn tex-btn-edit" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route(($routePrefix ?? 'admin.umrah.inclusions') . '.destroy', $inclusion->id) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="tex-btn tex-btn-delete" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <div class="tex-empty">
                                <div class="tex-empty-icon"><i class="bi bi-check-circle"></i></div>
                                <h5>No inclusions found</h5>
                                <p>Add your first inclusion to get started.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($inclusions->hasPages())
            <div class="tex-pagination">
                {{ $inclusions->links() }}
            </div>
        @endif
    </div>

    <!-- ===== ADD MODAL ===== -->
    <div id="addModal" class="tex-modal-overlay hidden">
        <div class="tex-modal">
            <form action="{{ route(($routePrefix ?? 'admin.umrah.inclusions') . '.store') }}" method="POST">
                @csrf
                <div class="tex-modal-header">
                    <h5>Add Inclusion</h5>
                    <button type="button" onclick="closeModal('addModal')" class="tex-modal-close">&times;</button>
                </div>
                <div class="tex-modal-body">
                    <div class="tex-field">
                        <label>Inclusion Name</label>
                        <input type="text" name="name" class="form-control"
                               placeholder="e.g., Flight Tickets, Hotel Service" required>
                    </div>
                </div>
                <div class="tex-modal-footer">
                    <button type="button" onclick="closeModal('addModal')" class="tex-btn tex-btn-cancel">Cancel</button>
                    <button type="submit" class="tex-btn tex-btn-primary">Add Inclusion</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== EDIT MODALS ===== -->
    @foreach($inclusions as $inclusion)
        <div id="editModal{{ $inclusion->id }}" class="tex-modal-overlay hidden">
            <div class="tex-modal">
                <form action="{{ route(($routePrefix ?? 'admin.umrah.inclusions') . '.update', $inclusion->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="tex-modal-header">
                        <h5>Edit Inclusion</h5>
                        <button type="button" onclick="closeModal('editModal{{ $inclusion->id }}')" class="tex-modal-close">&times;</button>
                    </div>
                    <div class="tex-modal-body">
                        <div class="tex-field">
                            <label>Inclusion Name</label>
                            <input type="text" name="name" class="form-control"
                                   value="{{ $inclusion->name }}" required>
                        </div>
                    </div>
                    <div class="tex-modal-footer">
                        <button type="button" onclick="closeModal('editModal{{ $inclusion->id }}')" class="tex-btn tex-btn-cancel">Cancel</button>
                        <button type="submit" class="tex-btn tex-btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

@endsection


@push('scripts')
    <script>
        function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
        function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('tex-modal-overlay')) closeModal(e.target.id);
        });
    </script>
@endpush
