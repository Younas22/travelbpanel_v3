@extends($layout ?? 'admin.layouts.app')
@section('title', 'Umrah Package Types')

@section('content')

    <!-- ===== BREADCRUMB ===== -->
    <div class="tex-breadcrumb">
        <a href="{{ route('agent.dashboard') }}">Dashboard</a>
        <i class="bi bi-chevron-right"></i>
        <a href="{{ route('agent.umrah.index') }}">My Umrah</a>
        <i class="bi bi-chevron-right"></i>
        <span>Package Types</span>
    </div>

    <!-- ===== PAGE HEADER ===== -->
    <div class="tex-header">
        <div class="tex-header-left">
            <div class="tex-header-icon"><i class="bi bi-tags"></i></div>
            <div>
                <h2 class="tex-title">Umrah Package Types</h2>
                <p class="tex-subtitle">Manage package types for Umrah packages</p>
            </div>
        </div>
        <button onclick="openModal('addModal')" class="tex-add-btn">
            <i class="bi bi-plus-circle"></i> Add Package Type
        </button>
    </div>

    <!-- ===== TABLE CARD ===== -->
    <div class="tex-card">
        <div class="table-responsive">
            <table class="tex-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Package Type</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($packageTypes as $type)
                    <tr>
                        <td><span class="tex-meta">{{ $type->id }}</span></td>
                        <td><span class="tex-name">{{ ucfirst($type->packege_type) }}</span></td>
                        <td>
                            @if($type->status == '1')
                                <span class="tex-badge tex-badge-active">Active</span>
                            @else
                                <span class="tex-badge tex-badge-inactive">Inactive</span>
                            @endif
                        </td>
                        <td><span class="tex-meta">{{ $type->created_at->format('d M Y') }}</span></td>
                        <td>
                            <div class="tex-actions">
                                <button onclick="openModal('editModal{{ $type->id }}')"
                                        class="tex-btn tex-btn-edit" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route(($routePrefix ?? 'admin.umrah.package-types') . '.toggle-status', $type->id) }}"
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="tex-btn {{ $type->status == '1' ? 'tex-btn-pause' : 'tex-btn-activate' }}"
                                            title="{{ $type->status == '1' ? 'Deactivate' : 'Activate' }}">
                                        <i class="bi bi-{{ $type->status == '1' ? 'pause-circle' : 'play-circle' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route(($routePrefix ?? 'admin.umrah.package-types') . '.destroy', $type->id) }}"
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
                        <td colspan="5">
                            <div class="tex-empty">
                                <div class="tex-empty-icon"><i class="bi bi-tags"></i></div>
                                <h5>No package types found</h5>
                                <p>Add your first package type to get started.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($packageTypes->hasPages())
            <div class="tex-pagination">
                {{ $packageTypes->links() }}
            </div>
        @endif
    </div>

    <!-- ===== ADD MODAL ===== -->
    <div id="addModal" class="tex-modal-overlay hidden">
        <div class="tex-modal">
            <form action="{{ route(($routePrefix ?? 'admin.umrah.package-types') . '.store') }}" method="POST">
                @csrf
                <div class="tex-modal-header">
                    <h5>Add Package Type</h5>
                    <button type="button" onclick="closeModal('addModal')" class="tex-modal-close">&times;</button>
                </div>
                <div class="tex-modal-body">
                    <div class="tex-field">
                        <label>Package Type Name</label>
                        <input type="text" name="packege_type" class="form-control"
                               placeholder="e.g., basic, standard, premium" required>
                    </div>
                    <div class="tex-field">
                        <label>Status</label>
                        <select name="status" class="form-select">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="tex-modal-footer">
                    <button type="button" onclick="closeModal('addModal')" class="tex-btn tex-btn-cancel">Cancel</button>
                    <button type="submit" class="tex-btn tex-btn-primary">Add Package Type</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== EDIT MODALS ===== -->
    @foreach($packageTypes as $type)
        <div id="editModal{{ $type->id }}" class="tex-modal-overlay hidden">
            <div class="tex-modal">
                <form action="{{ route(($routePrefix ?? 'admin.umrah.package-types') . '.update', $type->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="tex-modal-header">
                        <h5>Edit Package Type</h5>
                        <button type="button" onclick="closeModal('editModal{{ $type->id }}')" class="tex-modal-close">&times;</button>
                    </div>
                    <div class="tex-modal-body">
                        <div class="tex-field">
                            <label>Package Type Name</label>
                            <input type="text" name="packege_type" class="form-control"
                                   value="{{ $type->packege_type }}" required>
                        </div>
                        <div class="tex-field">
                            <label>Status</label>
                            <select name="status" class="form-select">
                                <option value="1" {{ $type->status == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ $type->status == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="tex-modal-footer">
                        <button type="button" onclick="closeModal('editModal{{ $type->id }}')" class="tex-btn tex-btn-cancel">Cancel</button>
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
