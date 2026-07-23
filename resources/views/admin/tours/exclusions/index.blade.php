@extends($layout ?? 'admin.layouts.app')
@section('title', 'Tour Exclusions')

@section('content')

    <!-- ===== BREADCRUMB ===== -->
    <div class="tex-breadcrumb">
        <a href="{{ route('agent.dashboard') }}">Dashboard</a>
        <i class="bi bi-chevron-right"></i>
        <a href="{{ route('agent.tours.index') }}">My Tours</a>
        <i class="bi bi-chevron-right"></i>
        <span>Exclusions</span>
    </div>

    <!-- ===== PAGE HEADER ===== -->
    <div class="tex-header">
        <div class="tex-header-left">
            <div class="tex-header-icon"><i class="bi bi-x-circle"></i></div>
            <div>
                <h2 class="tex-title">Tour Exclusions</h2>
                <p class="tex-subtitle">Manage exclusions for tour packages</p>
            </div>
        </div>
        <button onclick="openModal('addModal')" class="tex-add-btn">
            <i class="bi bi-plus-circle"></i> Add Exclusion
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
                @forelse($exclusions as $exclusion)
                    <tr>
                        <td><span class="tex-meta">{{ $exclusion->id }}</span></td>
                        <td><span class="tex-name">{{ $exclusion->name }}</span></td>
                        <td><span class="tex-meta">{{ $exclusion->created_at->format('d M Y') }}</span></td>
                        <td>
                            <div class="tex-actions">
                                <button onclick="openModal('editModal{{ $exclusion->id }}')" class="tex-btn tex-btn-edit" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route(($routePrefix ?? 'admin.tours.exclusions') . '.destroy', $exclusion->id) }}"
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
                                <div class="tex-empty-icon"><i class="bi bi-x-circle"></i></div>
                                <h5>No exclusions found</h5>
                                <p>Add your first exclusion to get started.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($exclusions->hasPages())
            <div class="tex-pagination">
                {{ $exclusions->links() }}
            </div>
        @endif
    </div>

    <!-- ===== ADD MODAL ===== -->
    <div id="addModal" class="tex-modal-overlay hidden">
        <div class="tex-modal">
            <form action="{{ route(($routePrefix ?? 'admin.tours.exclusions') . '.store') }}" method="POST">
                @csrf
                <div class="tex-modal-header">
                    <h5>Add Exclusion</h5>
                    <button type="button" onclick="closeModal('addModal')" class="tex-modal-close">&times;</button>
                </div>
                <div class="tex-modal-body">
                    <div class="tex-field">
                        <label>Exclusion Name</label>
                        <input type="text" name="name" class="form-control"
                               placeholder="e.g., Additional Meals, Room Extras" required>
                    </div>
                </div>
                <div class="tex-modal-footer">
                    <button type="button" onclick="closeModal('addModal')" class="tex-btn tex-btn-cancel">Cancel</button>
                    <button type="submit" class="tex-btn tex-btn-primary">Add Exclusion</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== EDIT MODALS ===== -->
    @foreach($exclusions as $exclusion)
        <div id="editModal{{ $exclusion->id }}" class="tex-modal-overlay hidden">
            <div class="tex-modal">
                <form action="{{ route(($routePrefix ?? 'admin.tours.exclusions') . '.update', $exclusion->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="tex-modal-header">
                        <h5>Edit Exclusion</h5>
                        <button type="button" onclick="closeModal('editModal{{ $exclusion->id }}')" class="tex-modal-close">&times;</button>
                    </div>
                    <div class="tex-modal-body">
                        <div class="tex-field">
                            <label>Exclusion Name</label>
                            <input type="text" name="name" class="form-control"
                                   value="{{ $exclusion->name }}" required>
                        </div>
                    </div>
                    <div class="tex-modal-footer">
                        <button type="button" onclick="closeModal('editModal{{ $exclusion->id }}')" class="tex-btn tex-btn-cancel">Cancel</button>
                        <button type="submit" class="tex-btn tex-btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

@endsection


@push('scripts')
    <script>
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }
        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('tex-modal-overlay')) {
                closeModal(e.target.id);
            }
        });
    </script>
@endpush
